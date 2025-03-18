<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Деталі замовлення</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #b5b5b5;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .header,
        .summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }

        .logo {
            width: 200px;
        }

        h1 {
            font-size: 28px;
            color: #6b1f1f;
            margin-bottom: 20px;
            text-align: center;
        }

        .details p,
        .intro p,
        .product-price,
        .summary p {
            margin: 10px 0;
            font-size: 16px;
            color: #374151;
        }

        .important,
        .product-name {
            font-weight: 700;
            color: #1f2937;
        }

        .products {
            margin-top: 25px;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background-color: #f9fafb;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }

        .product-info {
            flex: 1;
        }

        .product-price,
        .product-total {
            font-size: 16px;
            color: #6b7280;
        }

        .product-total {
            font-weight: 700;
            color: #6b1f1f;
            margin-left: auto;
        }

        .total-price {
            font-size: 22px;
            font-weight: 700;
            color: #6b1f1f;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin-top: 15px;
        }

        .footer a {
            color: #1d4ed8;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Logo" class="logo">
            <!-- <div class="store-name">Коштовня</div> -->
        </div>

        <div class="intro">
            <p>
                Вітаємо, {{ $order->first_name }}!<br>
                Дякуємо за замовлення <b>№{{ $order->id }}</b> створене <b>{{ $order->created_at }}</b> в інтернет-магазині Коштовня.
                Незабаром ми розпочнемо його реалізацію. Коли замовлення буде відправлено, Вам надійде нове повідомлення.
            </p>
        </div>

        <h1>Деталі замовлення</h1>

        <div class="details">
            <p><span class="important">Ім'я покупця:</span> {{ $order->first_name }} {{ $order->last_name }} {{ $order->second_name }} </p>
            <p><span class="important">Номер телефону:</span> {{ $order->phone_number }}</p>
            <p><span class="important">Спосіб доставки:</span> {{ $delivery->deliveryType->name }}</p>
            <p><span class="important">Адреса доставки:</span> {{ $delivery->city }}, {{ $delivery->delivery_address }}</p>
            <p><span class="important">Метод оплати:</span> {{ $payment->payment_method }}</p>
            <p><span class="important">Накладна для відстеження:</span> {{ $order->waybill }}</p>
        </div>

        <div class="products">
            @foreach ($order->products as $product)
            <div class="product-item">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image">
                <div class="product-info">
                    <p class="product-name">{{ $product->name }}</p>
                    <p class="product-price">{{ number_format($product->price, 2) }} х {{ $product->pivot->quantity }} шт.</p>
                </div>
                <p class="product-total">{{ number_format($product->price * $product->pivot->quantity, 2) }} грн</p>
            </div>
            @endforeach
        </div>

        <div class="summary">
            <p>Вартість доставки: <span class="total-price">{{ number_format($delivery->cost, 2) }} грн</span></p>
        </div>

        <div class="summary">
            <p>Загальна сума: <span class="total-price">{{ number_format($order->total_amount, 2) }} грн</span></p>
        </div>

        <div class="footer">
            Дякуємо за ваше замовлення!<br>
            Якщо у вас є запитання, звертайтеся до нас!.
        </div>
    </div>
</body>

</html>