<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Замовлення відправлено</title>
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

        .header {
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

        .title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            text-align: center;
            margin-bottom: 20px;
        }

        .message {
            font-size: 18px;
            color: #374151;
            text-align: center;
            margin-bottom: 20px;
        }

        .order-summary {
            padding: 20px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .order-summary p {
            font-size: 16px;
            color: #374151;
            margin: 10px 0;
        }

        .footer {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Logo" class="logo">
        </div>

        <div class="title">
            Вітаємо, {{ $order->last_name }} {{ $order->first_name }},
        </div>

        <div class="message">
            Дякуємо за Ваше замовлення! Ваша посилка ретельно упакована і вже відправлена.
            Очікуйте на повідомлення про доставку посилки до вказаного місця.
        </div>

        <div class="order-summary">
            <p><strong>Номер замовлення:</strong> {{ $order->id }}</p>
            <p><strong>Номер для відстеження посилки:</strong> {{ $order->waybill }}</p>
            <p><strong>Дата відправлення:</strong> {{ $order->updated_at }}</p>
            <p><strong>Сума:</strong> {{ number_format($order->total_amount, 2) }} грн</p>
        </div>
        <div class="footer">
            Дякуємо за ваше замовлення!<br>
            Якщо у вас є запитання, звертайтеся до нас!<br>
            <a href="https://koshtovnya-front-end-33rd.vercel.app/homepage" style="color: blue; text-decoration: underline;">
                Перейти на сайт
            </a>
        </div>

    </div>
</body>

</html>