<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вітаємо!</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #b5b5b5;
            color: #111827;
            margin: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .logo {
            width: 200px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            color: #6b1f1f;
        }

        p {
            font-size: 16px;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="Logo" class="logo">
        <h1>Привіт, {{ $user->first_name }}!</h1>
        <p>Дякуємо, що зареєструвалися в нашому магазині.</p>
        <p>Раді бачити вас серед наших клієнтів!</p>
        <a href="https://koshtovnya-front-end-33rd.vercel.app/homepage" style="color: blue; text-decoration: underline;">
                Перейти на сайт
            </a>
    </div>
</body>
</html>
