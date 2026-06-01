require('dotenv').config();
const { Client, GatewayIntentBits, REST, Routes, ActivityType } = require('discord.js');
const fs = require('fs');
const path = require('path');

const dbPath = path.join(__dirname, 'database.json');

// Helper to load db
function loadDB() {
    if (!fs.existsSync(dbPath)) return {};
    const data = fs.readFileSync(dbPath, 'utf8');
    try {
        return JSON.parse(data);
    } catch (e) {
        return {};
    }
}

// Helper to save db
function saveDB(data) {
    fs.writeFileSync(dbPath, JSON.stringify(data, null, 2));
}

// Initialize client with privileged intents
const client = new Client({
    intents: [
        GatewayIntentBits.Guilds,
        GatewayIntentBits.GuildPresences,
        GatewayIntentBits.GuildMembers,
        GatewayIntentBits.GuildMessages
    ]
});

// Register Slash Commands
const commands = [
    {
        name: 'lastseen',
        description: 'Melihat kapan user terakhir kali online/terlihat',
        options: [
            {
                name: 'user',
                type: 6, // USER type
                description: 'User yang ingin dicek',
                required: true,
            },
        ],
    },
    {
        name: 'setup',
        description: 'Setup konfigurasi bot',
        options: [
            {
                name: 'x-rcs',
                type: 1, // SUB_COMMAND
                description: 'Setup feed X/Twitter monitor untuk dikirim ke channel Discord',
                options: [
                    {
                        name: 'username',
                        type: 3, // STRING
                        description: 'Username X/Twitter Anda',
                        required: true,
                    },
                    {
                        name: 'password',
                        type: 3, // STRING
                        description: 'Password X/Twitter Anda',
                        required: true,
                    },
                    {
                        name: 'email',
                        type: 3, // STRING
                        description: 'Email X/Twitter Anda',
                        required: true,
                    },
                    {
                        name: 'channel',
                        type: 7, // CHANNEL
                        description: 'Channel Discord untuk mengirim tweet',
                        required: true,
                    },
                    {
                        name: 'two_factor_secret',
                        type: 3, // STRING
                        description: '2FA/TOTP Secret Key (opsional jika 2FA aktif)',
                        required: false,
                    },
                    {
                        name: 'ping_role',
                        type: 8, // ROLE
                        description: 'Role yang akan diping (opsional, contoh: @everyone)',
                        required: false,
                    }
                ]
            },
            {
                name: 'x-cookies',
                type: 1, // SUB_COMMAND
                description: 'Setup feed X/Twitter monitor menggunakan Session Cookies',
                options: [
                    {
                        name: 'auth_token',
                        type: 3, // STRING
                        description: 'Nilai cookie auth_token dari browser',
                        required: true,
                    },
                    {
                        name: 'ct0',
                        type: 3, // STRING
                        description: 'Nilai cookie ct0 dari browser',
                        required: true,
                    },
                    {
                        name: 'channel',
                        type: 7, // CHANNEL
                        description: 'Channel Discord untuk mengirim tweet',
                        required: true,
                    },
                    {
                        name: 'twid',
                        type: 3, // STRING
                        description: 'Nilai cookie twid dari browser (opsional)',
                        required: false,
                    },
                    {
                        name: 'ping_role',
                        type: 8, // ROLE
                        description: 'Role yang akan diping (opsional, contoh: @everyone)',
                        required: false,
                    }
                ]
            }
        ]
    }
];

const rest = new REST({ version: '10' }).setToken(process.env.DISCORD_TOKEN);

client.once('ready', async () => {
    console.log(`Bot logged in as ${client.user.tag}`);
    client.user.setActivity('Abuser', { type: ActivityType.Watching });

    try {
        console.log('Started refreshing application (/) commands.');
        await rest.put(
            Routes.applicationCommands(process.env.CLIENT_ID),
            { body: commands }
        );
        console.log('Successfully reloaded application (/) commands.');
    } catch (error) {
        console.error(error);
    }

    // Initialize Twitter Monitor
    initTwitter(client).catch(err => {
        console.error('[Twitter Monitor] Critical initialization error:', err);
    });
});

// Update database when presence changes
client.on('presenceUpdate', (oldPresence, newPresence) => {
    if (!newPresence || !newPresence.user) return;
    if (newPresence.user.bot) return; // Ignore bots
    
    // We only care if status changes to offline, or if they are online we update their timestamp
    const db = loadDB();
    const userId = newPresence.userId;
    
    db[userId] = {
        last_seen: Date.now(),
        status: newPresence.status, // online, idle, dnd, offline
        username: newPresence.user.tag
    };
    
    saveDB(db);
});

// Handle Slash Commands
client.on('interactionCreate', async interaction => {
    if (!interaction.isChatInputCommand()) return;

    if (interaction.commandName === 'lastseen') {
        const targetUser = interaction.options.getUser('user');
        
        // If targeting a bot
        if (targetUser.bot) {
            return interaction.reply({ content: 'Bot selalu online atau tidak bisa dilacak last seen-nya!', ephemeral: true });
        }

        // Fetch current presence from cache
        const member = interaction.guild.members.cache.get(targetUser.id);
        let currentStatus = member?.presence?.status || 'offline';
        
        // If user is currently online/idle/dnd, they are seen right now
        if (['online', 'idle', 'dnd'].includes(currentStatus)) {
            // Update db for current
            const db = loadDB();
            db[targetUser.id] = {
                last_seen: Date.now(),
                status: currentStatus,
                username: targetUser.tag
            };
            saveDB(db);
            
            return interaction.reply({ 
                content: `🟢 **${targetUser.username}** saat ini sedang **${currentStatus.toUpperCase()}**.`,
                allowedMentions: { repliedUser: false } 
            });
        }

        // Check database
        const db = loadDB();
        const userData = db[targetUser.id];

        let lastSeenText = 'Belum ada riwayat aktivitas (offline sejak bot ini dihidupkan).';
        
        if (userData && userData.last_seen) {
            const timestamp = Math.floor(userData.last_seen / 1000);
            // <t:timestamp:F> for full date/time, <t:timestamp:R> for relative time
            lastSeenText = `<t:${timestamp}:F> (<t:${timestamp}:R>)`;
        }

        const embed = {
            title: '🔍 Last Seen Tracker',
            color: 0x2b2d31,
            thumbnail: { url: targetUser.displayAvatarURL() },
            fields: [
                {
                    name: '👤 User',
                    value: `<@${targetUser.id}>`,
                    inline: true
                },
                {
                    name: '📶 Status Terakhir',
                    value: `⚫ OFFLINE`,
                    inline: true
                },
                {
                    name: '🕒 Terakhir Terlihat',
                    value: lastSeenText,
                    inline: false
                }
            ],
            footer: {
                text: 'Hanya mencatat sejak bot dijalankan'
            }
        };

        await interaction.reply({ embeds: [embed] });
    } else if (interaction.commandName === 'setup') {
        const subcommand = interaction.options.getSubcommand();
        if (subcommand === 'x-rcs') {
            const username = interaction.options.getString('username');
            const password = interaction.options.getString('password');
            const email = interaction.options.getString('email');
            const channel = interaction.options.getChannel('channel');
            const twoFactorSecret = interaction.options.getString('two_factor_secret');
            const pingRole = interaction.options.getRole('ping_role');

            // Ephemeral reply so credentials are not exposed in public chat
            await interaction.deferReply({ ephemeral: true });

            try {
                // Save config to database.json
                const db = loadDB();
                
                // Keep existing user tracking data intact, only set/update twitter_config
                db.twitter_config = {
                    username,
                    password,
                    email,
                    two_factor_secret: twoFactorSecret || undefined,
                    channel_id: channel.id,
                    ping_role: pingRole ? (pingRole.name === '@everyone' ? '@everyone' : `<@&${pingRole.id}>`) : '@everyone'
                };
                
                saveDB(db);

                await interaction.editReply({ content: '⚙️ Konfigurasi disimpan! Menghubungkan ke Twitter/X secara dinamis...' });

                // Dynamically re-initialize Twitter scraper
                const success = await initTwitter(interaction.client);
                if (success) {
                    await interaction.followUp({ content: '✅ Berhasil login ke Twitter/X! Bot sekarang memantau feed beranda Anda secara otomatis.', ephemeral: true });
                } else {
                    await interaction.followUp({ content: '❌ Gagal login ke Twitter/X. Periksa username, password, email, atau 2FA Secret Anda (dan pastikan tidak ada verifikasi email yang memblokir login baru).', ephemeral: true });
                }
            } catch (error) {
                console.error('[Twitter Command] Setup failed:', error);
                await interaction.followUp({ content: `❌ Terjadi kesalahan saat melakukan inisialisasi: ${error.message}`, ephemeral: true });
            }
        } else if (subcommand === 'x-cookies') {
            const authToken = interaction.options.getString('auth_token');
            const ct0 = interaction.options.getString('ct0');
            const twid = interaction.options.getString('twid');
            const channel = interaction.options.getChannel('channel');
            const pingRole = interaction.options.getRole('ping_role');

            await interaction.deferReply({ ephemeral: true });

            try {
                const db = loadDB();
                
                db.twitter_config = {
                    auth_token: authToken,
                    ct0: ct0,
                    twid: twid || undefined,
                    channel_id: channel.id,
                    ping_role: pingRole ? (pingRole.name === '@everyone' ? '@everyone' : `<@&${pingRole.id}>`) : '@everyone'
                };
                
                saveDB(db);

                await interaction.editReply({ content: '⚙️ Konfigurasi cookie disimpan! Menghubungkan ke Twitter/X...' });

                const success = await initTwitter(interaction.client);
                if (success) {
                    await interaction.followUp({ content: '✅ Berhasil login menggunakan Cookie! Bot sekarang memantau feed beranda Anda secara otomatis.', ephemeral: true });
                } else {
                    await interaction.followUp({ content: '❌ Gagal login ke Twitter/X menggunakan Cookie. Pastikan cookie `auth_token` dan `ct0` yang Anda masukkan valid dan belum kedaluwarsa.', ephemeral: true });
                }
            } catch (error) {
                console.error('[Twitter Command] Cookie setup failed:', error);
                await interaction.followUp({ content: `❌ Terjadi kesalahan saat melakukan inisialisasi cookie: ${error.message}`, ephemeral: true });
            }
        }
    }
});

client.login(process.env.DISCORD_TOKEN);

// ==========================================
// Twitter/X Feed Integration
// ==========================================

let twitterScraper = null;
let twitterLoggedIn = false;
let twitterIntervalId = null;
const profileCache = {};

const seenTweetsPath = path.join(__dirname, 'seen_tweets.json');

function loadSeenTweets() {
    if (!fs.existsSync(seenTweetsPath)) return null;
    try {
        const data = fs.readFileSync(seenTweetsPath, 'utf8');
        return new Set(JSON.parse(data));
    } catch (e) {
        return new Set();
    }
}

function saveSeenTweets(seenSet) {
    try {
        fs.writeFileSync(seenTweetsPath, JSON.stringify(Array.from(seenSet), null, 2));
    } catch (e) {
        console.error('[Twitter Monitor] Failed to save seen tweets:', e.message);
    }
}

async function getUserProfile(scraper, username) {
    const now = Date.now();
    if (profileCache[username] && (now - profileCache[username].fetchedAt < 24 * 60 * 60 * 1000)) {
        return profileCache[username].data;
    }
    
    try {
        console.log(`[Twitter Monitor] Fetching profile for @${username}...`);
        const profile = await scraper.getProfile(username);
        profileCache[username] = {
            fetchedAt: now,
            data: profile
        };
        return profile;
    } catch (e) {
        console.error(`[Twitter Monitor] Failed to fetch profile for @${username}:`, e.message);
        return {
            name: username,
            avatar: 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png'
        };
    }
}

async function checkNewTweets(client) {
    if (!twitterLoggedIn || !twitterScraper) return;

    let seenTweets = loadSeenTweets();
    const isFirstRun = (seenTweets === null);
    if (isFirstRun) {
        seenTweets = new Set();
        console.log('[Twitter Monitor] First run detected. Initializing seen tweets database...');
    }

    try {
        console.log('[Twitter Monitor] Checking for new tweets...');
        // Fetch latest 20 tweets from home timeline
        const tweets = await twitterScraper.fetchHomeTimeline(20, Array.from(seenTweets));
        
        if (!tweets || !Array.isArray(tweets)) {
            console.log('[Twitter Monitor] No tweets retrieved or invalid response.');
            return;
        }

        // Sort tweets chronologically by Snowflake ID
        const sortedTweets = tweets.sort((a, b) => {
            try {
                const aId = BigInt(a.id);
                const bId = BigInt(b.id);
                return aId > bId ? 1 : (aId < bId ? -1 : 0);
            } catch (e) {
                return 0;
            }
        });

        const newTweetsToSend = [];

        for (const tweet of sortedTweets) {
            if (!tweet || !tweet.id) continue;
            
            // Skip if already seen
            if (seenTweets.has(tweet.id)) continue;
            
            // Mark as seen
            seenTweets.add(tweet.id);
            
            // If it's a first run (cold start), we just cache the ID but do not send
            if (isFirstRun) continue;

            // Skip replies and retweets
            if (tweet.isReply) continue;
            if (tweet.isRetweet) continue;

            newTweetsToSend.push(tweet);
        }

        // Save updated seen tweets list
        saveSeenTweets(seenTweets);

        if (newTweetsToSend.length > 0) {
            console.log(`[Twitter Monitor] Found ${newTweetsToSend.length} new tweets to send.`);
            
            const db = loadDB();
            const config = db.twitter_config || {};
            const channelId = config.channel_id || process.env.TWITTER_CHANNEL_ID;
            
            const channel = await client.channels.fetch(channelId).catch(err => {
                console.error(`[Twitter Monitor] Failed to fetch channel ${channelId}:`, err.message);
                return null;
            });

            if (channel) {
                const pingRole = config.ping_role !== undefined ? config.ping_role : (process.env.TWITTER_PING_ROLE || '@everyone');
                const content = pingRole ? `${pingRole} Tweeted` : '';

                for (const tweet of newTweetsToSend) {
                    const profile = await getUserProfile(twitterScraper, tweet.username);

                    let imageUrl = null;
                    if (tweet.photos && tweet.photos.length > 0) {
                        const photo = tweet.photos[0];
                        imageUrl = typeof photo === 'string' ? photo : (photo.url || photo.src);
                    }

                    // Parse timestamp safely
                    let tweetTime = Date.now();
                    if (tweet.timestamp) {
                        if (typeof tweet.timestamp === 'number') {
                            tweetTime = tweet.timestamp < 9999999999 ? tweet.timestamp * 1000 : tweet.timestamp;
                        } else {
                            const parsed = Date.parse(tweet.timestamp);
                            if (!isNaN(parsed)) tweetTime = parsed;
                        }
                    }

                    const embed = {
                        author: {
                            name: `${profile.name || tweet.name || tweet.username} (@${tweet.username})`,
                            url: tweet.permanentUrl || `https://x.com/${tweet.username}/status/${tweet.id}`,
                            icon_url: profile.avatar || profile.avatarUrl || 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png'
                        },
                        description: tweet.text || '',
                        url: tweet.permanentUrl || `https://x.com/${tweet.username}/status/${tweet.id}`,
                        color: 0x1da1f2, // Twitter Blue
                        timestamp: new Date(tweetTime).toISOString(),
                        footer: {
                            text: 'X/Twitter Monitor'
                        }
                    };

                    if (imageUrl) {
                        embed.image = { url: imageUrl };
                    }

                    await channel.send({
                        content: content || undefined,
                        embeds: [embed]
                    }).catch(err => console.error('[Twitter Monitor] Failed to send message to Discord:', err.message));
                }
            }
        } else {
            console.log('[Twitter Monitor] No new tweets since last check.');
        }
    } catch (error) {
        console.error('[Twitter Monitor] Error fetching or processing home timeline:', error.message);
    }
}

async function initTwitter(client) {
    const db = loadDB();
    const config = db.twitter_config || {};

    const username = config.username || process.env.TWITTER_USERNAME;
    const password = config.password || process.env.TWITTER_PASSWORD;
    const email = config.email || process.env.TWITTER_EMAIL;
    const channelId = config.channel_id || process.env.TWITTER_CHANNEL_ID;
    const twoFactorSecret = config.two_factor_secret || process.env.TWITTER_2FA_SECRET;

    const hasCookiesConfig = !!(config.auth_token && config.ct0);
    const hasCredentialsConfig = !!(username && username !== 'your_username_here' && password && email);

    if (!channelId) {
        console.log('[Twitter Monitor] Channel ID not configured. Feature disabled.');
        twitterLoggedIn = false;
        twitterScraper = null;
        return false;
    }

    if (!hasCookiesConfig && !hasCredentialsConfig) {
        console.log('[Twitter Monitor] Neither cookies nor credentials are fully configured in database or .env. Feature disabled.');
        twitterLoggedIn = false;
        twitterScraper = null;
        return false;
    }

    try {
        console.log('[Twitter Monitor] Initializing Twitter scraper...');
        const { Scraper } = await import('agent-twitter-client');
        const scraper = new Scraper();
        
        const cookiesPath = path.join(__dirname, 'twitter_cookies.json');
        
        // If cookies are in config, override/generate cookies file
        if (hasCookiesConfig) {
            // Set cookies for BOTH twitter.com and x.com since the platform uses both domains.
            // The library connects to api.twitter.com but browser cookies are for x.com.
            const domainsToSet = ['.twitter.com', '.x.com'];
            const cookiesArray = [];
            for (const domain of domainsToSet) {
                cookiesArray.push(`auth_token=${config.auth_token}; Domain=${domain}; Path=/; Secure; HttpOnly; SameSite=None`);
                cookiesArray.push(`ct0=${config.ct0}; Domain=${domain}; Path=/; Secure; SameSite=None`);
                if (config.twid) {
                    cookiesArray.push(`twid=${config.twid}; Domain=${domain}; Path=/; Secure; SameSite=None`);
                }
            }
            fs.writeFileSync(cookiesPath, JSON.stringify(cookiesArray, null, 2));
            console.log('[Twitter Monitor] Cookies written from database config (both twitter.com + x.com).');
        }

        // Try loading cookies
        let cookiesLoaded = false;
        if (fs.existsSync(cookiesPath)) {
            try {
                const cookiesData = fs.readFileSync(cookiesPath, 'utf8');
                const cookies = JSON.parse(cookiesData);
                await scraper.setCookies(cookies);
                cookiesLoaded = true;
                console.log('[Twitter Monitor] Loaded cookies from file.');
            } catch (e) {
                console.error('[Twitter Monitor] Failed to load or parse cookies:', e.message);
            }
        }

        if (cookiesLoaded && hasCookiesConfig) {
            // When using manual browser cookies, skip isLoggedIn() entirely.
            // The verify_credentials v1.1 API endpoint is unreliable and frequently
            // returns false negatives for manually-provided session cookies.
            // The polling cycle will naturally surface any real auth errors.
            console.log('[Twitter Monitor] Cookie mode: skipping isLoggedIn() check. Trusting provided cookies.');
        } else if (!cookiesLoaded) {
            // No cookies found at all — must login with credentials
            if (hasCredentialsConfig) {
                console.log('[Twitter Monitor] No cookies found. Logging in with credentials...');
                if (twoFactorSecret) {
                    console.log('[Twitter Monitor] Logging in with 2FA/TOTP enabled...');
                    await scraper.login(username, password, email, twoFactorSecret);
                } else {
                    await scraper.login(username, password, email);
                }
                const newCookies = await scraper.getCookies();
                fs.writeFileSync(cookiesPath, JSON.stringify(newCookies, null, 2));
                console.log('[Twitter Monitor] Successfully logged in with credentials and saved cookies.');
            } else {
                console.log('[Twitter Monitor] No cookies and no credentials configured. Login failed.');
                twitterLoggedIn = false;
                twitterScraper = null;
                return false;
            }
        } else {
            // Cookies loaded from a previous credential-login session — verify they are still valid
            try {
                const loggedIn = await scraper.isLoggedIn();
                if (!loggedIn) {
                    if (hasCredentialsConfig) {
                        console.log('[Twitter Monitor] Session cookies expired. Re-logging with credentials...');
                        try { fs.unlinkSync(cookiesPath); } catch(e) {}
                        if (twoFactorSecret) {
                            await scraper.login(username, password, email, twoFactorSecret);
                        } else {
                            await scraper.login(username, password, email);
                        }
                        const newCookies = await scraper.getCookies();
                        fs.writeFileSync(cookiesPath, JSON.stringify(newCookies, null, 2));
                        console.log('[Twitter Monitor] Re-login successful. Saved new cookies.');
                    } else {
                        console.log('[Twitter Monitor] Session cookies expired. No credential fallback configured. Login failed.');
                        twitterLoggedIn = false;
                        twitterScraper = null;
                        return false;
                    }
                } else {
                    console.log('[Twitter Monitor] Successfully authenticated via session cookies.');
                }
            } catch (e) {
                console.warn('[Twitter Monitor] Could not verify session, proceeding optimistically:', e.message);
            }
        }

        twitterScraper = scraper;
        twitterLoggedIn = true;
        
        // Clear existing interval if it exists
        if (twitterIntervalId) {
            clearInterval(twitterIntervalId);
            twitterIntervalId = null;
        }

        // Start polling
        const intervalMinutes = parseInt(process.env.TWITTER_CHECK_INTERVAL_MINUTES || '5', 10);
        const intervalMs = intervalMinutes * 60 * 1000;
        
        console.log(`[Twitter Monitor] Twitter polling started. Checking every ${intervalMinutes} minutes.`);
        
        // Run initial check (10s delay to let Discord bot finish starting up)
        setTimeout(() => {
            checkNewTweets(client).catch(err => console.error('[Twitter Monitor] Error in initial check:', err));
        }, 10000);
        
        twitterIntervalId = setInterval(() => {
            checkNewTweets(client).catch(err => console.error('[Twitter Monitor] Error in polling check:', err));
        }, intervalMs);

        return true;
    } catch (error) {
        console.error('[Twitter Monitor] Failed to initialize Twitter monitor:', error);
        twitterLoggedIn = false;
        twitterScraper = null;
        return false;
    }
}
