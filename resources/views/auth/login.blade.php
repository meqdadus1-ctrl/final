<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HRM Login</title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* LEFT */
        .left {
            width: 50%;
            background: url('/office.jpg') no-repeat center center/cover;
            position: relative;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.7);
            height: 100%;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
        }

        .logo {
            width: 80px;
            margin-bottom: 20px;
        }

        .overlay h1 {
            font-size: 40px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .overlay p {
            color: #ccc;
            max-width: 400px;
            line-height: 1.6;
        }

        /* RIGHT */
        .right {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f4f6fb;
        }

        .login-box {
            width: 350px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .login-box h2 {
            margin-bottom: 25px;
            color: #333;
            text-align: center;
        }

        /* INPUT */
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            outline: none;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        .input-group input:focus {
            border-color: #4a6cf7;
            box-shadow: 0 0 5px rgba(74,108,247,0.3);
        }

        .input-group label {
            position: absolute;
            top: 12px;
            left: 12px;
            color: #888;
            font-size: 14px;
            transition: 0.3s;
            pointer-events: none;
            background: white;
            padding: 0 5px;
        }

        .input-group input:focus + label,
        .input-group input:valid + label {
            top: -8px;
            font-size: 12px;
            color: #4a6cf7;
        }

        /* OPTIONS */
        .options {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .options a {
            text-decoration: none;
            color: #4a6cf7;
        }

        /* BUTTON */
        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: linear-gradient(135deg, #4a6cf7, #6a8cff);
            color: white;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* MOBILE */
        @media (max-width: 768px) {
            .left {
                display: none;
            }

            .right {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <!-- LEFT -->
    <div class="left">
        <div class="overlay">
            <img src="C:\laragon\www\hr-system\resources\images\logo.png" class="logo">
            <h1>FOX HRM</h1>
            <p>
                Smart Human Resource Management System to manage employees, payroll,
                attendance and performance efficiently.
            </p>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="right">
        <div class="login-box">

            <h2>Welcome Back</h2>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="input-group">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>

                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>

                <div class="options">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>

                    <a href="#">Forgot?</a>
                </div>

                <button type="submit">Login</button>

            </form>

        </div>
    </div>

</div>

</body>
</html>