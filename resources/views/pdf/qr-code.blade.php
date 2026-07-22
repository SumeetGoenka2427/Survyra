<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; text-align: center; padding: 40px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        p { color: #555; margin-top: 0; }
        .qr { margin: 30px auto; }
    </style>
</head>
<body>
    <h1>{{ $survey->client->company_name }}</h1>
    <p>{{ $label }}</p>
    <div class="qr">{!! $svg !!}</div>
    <p>Scan to share your feedback</p>
</body>
</html>
