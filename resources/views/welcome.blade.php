<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABUSER STORE</title>
    <style>
        /* --- RESET & BASIC --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #0b0b0b; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }

        /* --- HEADER --- */
        h1 { text-align: center; margin-bottom: 50px; font-weight: 300; letter-spacing: 2px; }
        h1 span { font-weight: bold; color: #00aaff; }

        /* --- GRID LAYOUT --- */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }

        /* --- CARD STYLE (MIRIP GAMBAR 1) --- */
        .card {
            background: #161616;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #222;
            cursor: pointer;
            display: flex; /* Biar logo di kiri, teks di kanan */
            align-items: center;
            height: 150px;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: #00aaff;
            box-shadow: 0 10px 30px rgba(0, 170, 255, 0.15);
        }

        /* Bagian Kiri (Logo/Banner) */
        .card-img {
            width: 40%;
            height: 100%;
            background: linear-gradient(135deg, #1e1e1e, #000);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Logo Placeholder (Lingkaran) */
        .logo-placeholder {
            width: 60px; height: 60px;
            border-radius: 50%;
            border: 2px solid #00aaff;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.5rem; color: #00aaff;
            box-shadow: 0 0 15px rgba(0, 170, 255, 0.3);
        }

        /* Bagian Kanan (Info) */
        .card-info {
            padding: 20px;
            width: 60%;
        }

        .card-info h3 { font-size: 1.5rem; margin-bottom: 5px; text-transform: uppercase; }
        .card-info p { color: #666; font-size: 0.9rem; }
        
        /* Label 'ITEMS' kecil */
        .badge-items {
            margin-top: 15px;
            display: inline-block;
            background: #222;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.7rem;
            color: #ccc;
            border: 1px solid #333;
        }

        /* Link Full Cover */
        .card-link {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>PREMIUM <span>CHEATS</span></h1>

        <div class="product-grid">
            @foreach($products as $product)
            <div class="card">
                <a href="{{ route('checkout', $product->id) }}" class="card-link"></a>
                
                <div class="card-img">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <div class="logo-placeholder">{{ substr($product->name, 0, 1) }}</div>
                    @endif
                </div>
                
                <div class="card-info">
                    <h3>{{ $product->name }}</h3>
                    <p>{{ $product->description }}</p>
                    <div class="badge-items">PC / STEAM</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</body>
</html>