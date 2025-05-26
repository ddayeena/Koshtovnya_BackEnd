<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вас заблоковано</title>
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
            {{ $user->first_name }},
        </div>

        <div class="message">
            Ви <strong>були заблоковані</strong> за порушення правил.<br>
            Якщо ви вважаєте, що це сталося помилково, будь ласка, зв'яжіться з нами за адресою:<br>
            <a href="mailto:koshtovnya.store@gmail.com">koshtovnya.store@gmail.com</a>
        </div>

        <div class="footer">
