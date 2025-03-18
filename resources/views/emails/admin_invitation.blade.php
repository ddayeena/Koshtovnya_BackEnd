<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запрошення стати адміністратором</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f3f4f6;
            color: #111827;
            margin: 0;
        }

        .container {
            max-width: 400px;
            margin: 30px auto;
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .header img {
            width: 200px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            color: #1f3a8a;
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            color: #374151;
            line-height: 1.5;
        }

        .details {
            font-size: 18px;
            font-weight: 700;
            background: #f3f4f6;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 10px;
            width: 100%;
            text-align: center;
        }

        .footer {
            font-size: 14px;
            color: #6b7280;
            margin-top: 30px;
        }

        .footer a {
            color: #1f3a8a;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Logo">
        </div>

        @if($admin->role == 'admin')
            <h1>Вас запросили стати адміністратором</h1>
            <p>Вітаємо! Ви отримали запрошення стати адміністратором інтернет-магазину "Коштовня".</p>
        @elseif($admin->role == 'manager')
            <h1>Вас запросили стати менеджером</h1>
            <p>Вітаємо! Ви отримали запрошення стати менеджером інтернет-магазину "Коштовня".</p>
        @elseif($admin->role == 'superadmin')
            <h1>Вас запросили стати суперадміном</h1>
            <p>Вітаємо! Ви отримали запрошення стати суперадміном інтернет-магазину "Коштовня".</p>
        @endif

        <div class="details">
            <p>Email: {{ $admin->email }}</p>
            <p>Пароль: {{ $password }}</p>
        </div>

        <p class="footer">Будь ласка, після входу змініть пароль на більш надійний. Раді бачити вас серед наших працівників!</p>

        <p class="footer">Якщо ви не реєструвалися, проігноруйте це повідомлення.</p>
    </div>
</body>

</html>
