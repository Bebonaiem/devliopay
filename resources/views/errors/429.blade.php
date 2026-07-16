<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Too Many Requests</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #0f172a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e2e8f0;
        }
        .container { text-align: center; padding: 40px; }
        .code {
            font-size: 120px;
            font-weight: 800;
            background: linear-gradient(135deg, #f59e0b, #eab308);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 16px;
        }
        .title { font-size: 24px; font-weight: 600; margin-bottom: 12px; color: #f1f5f9; }
        .message { font-size: 16px; color: #94a3b8; margin-bottom: 32px; max-width: 400px; margin-left: auto; margin-right: auto; }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.85; }
        .links { margin-top: 24px; }
        .links a { color: #818cf8; text-decoration: none; font-size: 14px; margin: 0 12px; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">429</div>
        <div class="title">Too Many Requests</div>
        <div class="message">You've made too many requests. Please wait a moment and try again.</div>
        <a href="javascript:location.reload()" class="btn">Try Again</a>
        <div class="links">
            <a href="/">Home</a>
            <a href="/login">Login</a>
        </div>
    </div>
</body>
</html>
