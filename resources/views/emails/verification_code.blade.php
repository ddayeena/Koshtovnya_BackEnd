<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код підтвердження</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #b5b5b5;
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
            font-size: 22px;
            color: #6b1f1f;
        }

        p {
            font-size: 16px;
            color: #374151;
        }

        .code {
            font-size: 24px;
            font-weight: 700;
            background: #f3f4f6;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Logo">
        </div>

        <h1>Код підтвердження</h1>
        <p>Ваш код:</p>
        <p class="code">{{ $code }}</p>
        <p>Дійсний 15 хвилин.</p>
        <p>Якщо ви не реєструвалися, проігноруйте це повідомлення.</p>
    </div>
</body>

</html>
