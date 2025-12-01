<?php
session_start();
// Database Connections
require_once 'system/database/db.php';
$pdo = db_connect();

// Handle AJAX login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!$username || !$password) {
        echo json_encode(['success'=>false, 'message'=>'Please fill in all fields']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username'=>$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check User Role Email and Username.
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'] ?? 'client';

        echo json_encode(['success'=>true, 'role'=>$_SESSION['role']]);
        exit();
    } else {
        echo json_encode(['success'=>false, 'message'=>'Invalid username or password']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
    * { box-sizing: border-box; margin:0; padding:0; }
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #191b33 0%, #0f1729 50%, #1a1d3a 100%);
        position: relative;
        overflow: hidden;
    }
    body::before {
        content:''; position:absolute; top:0; left:0; width:100%; height:100%;
        background: radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.15) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(79, 70, 229, 0.1) 0%, transparent 50%);
        z-index:0; pointer-events:none;
        animation: gradientShift 8s ease-in-out infinite;
    }
    body::after {
        content:''; position:absolute; top:0; left:0; width:100%; height:100%;
        background-image:
            repeating-linear-gradient(45deg, rgba(255,255,255,0.04), rgba(255,255,255,0.04) 1px, transparent 1px, transparent 20px),
            repeating-linear-gradient(-45deg, rgba(255,255,255,0.04), rgba(255,255,255,0.04) 1px, transparent 1px, transparent 20px);
        z-index:0; pointer-events:none;
    }
    @keyframes gradientShift { 0%,100%{opacity:1;} 50%{opacity:0.8;} }
    .login-wrapper {
        position: relative; z-index:1;
        background: #fff; width:420px;
        padding: 45px 40px; border-radius: 14px;
        box-shadow: 0 10px 35px rgba(0,0,0,0.15);
        animation: fadeIn 0.6s ease-out;
    }
    @keyframes fadeIn { from{opacity:0; transform:translateY(25px);} to{opacity:1; transform:translateY(0);} }
    .logo { display:block; margin:0 auto 20px; width:250px; height:auto; }
    .login-title { text-align:center; font-size:22px; color:#1f2937; font-weight:600; margin-bottom:10px; }
    .login-subtext { text-align:center; color:#6b7280; margin-bottom:30px; font-size:14px; }
    .input-group { position: relative; margin-bottom: 18px; }
    .input-group input {
        width:100%; padding:13px 16px 13px 48px; font-size:15px;
        border:1px solid #d1d5db; border-radius:10px; background:#f9fafb;
        transition:0.25s;
    }
    .input-group input:focus {
        border-color:#2563eb; background:#fff; box-shadow:0 0 6px rgba(37,99,235,0.25); outline:none;
    }
    .input-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); opacity:0.65; }
    .input-icon svg { width:20px; height:20px; fill:#4b5563; cursor:pointer; }
    .login-btn {
        width:100%; padding:13px; background:#001f63; color:white;
        font-size:16px; font-weight:600; border:none; border-radius:10px;
        cursor:pointer; transition:0.25s;
    }
    .login-btn:hover { background:#1e4ed8; transform:translateY(-2px); }
    .remember-forgot { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; font-size:14px; }
    .remember-forgot label { display:flex; align-items:center; gap:6px; cursor:pointer; }
    .remember-forgot a { color:#2563eb; text-decoration:none; transition:0.2s; }
    .remember-forgot a:hover { text-decoration:underline; }
    .error-message { color:red; font-size:13px; margin-bottom:10px; display:none; text-align:center; }
    .social-login-title { text-align:center; margin:25px 0 15px; font-size:14px; color:#6b7280; position:relative; }
    .social-login-title:before, .social-login-title:after {
        content:""; width:36%; height:1px; background:#e5e7eb; position:absolute; top:50%;
    }
    .social-login-title:before { left:0; }
    .social-login-title:after { right:0; }
    .social-btn {
        width:100%; padding:13px; background:white; border:1px solid #d1d5db;
        border-radius:10px; cursor:pointer; margin-bottom:12px;
        display:flex; align-items:center; justify-content:center; gap:12px; font-size:15px; transition:0.25s;
    }
    .social-btn:hover { background:#f3f4f6; transform:translateY(-2px); }
    .social-btn img { width:22px; height:22px; }
    .shake { animation: shake 0.4s; }
    @keyframes shake {
        0%{transform:translateX(0);} 25%{transform:translateX(-6px);} 50%{transform:translateX(6px);}
        75%{transform:translateX(-6px);} 100%{transform:translateX(0);}
    }
    .toggle-password { position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; opacity:0.65; }

    /* Chat Widget */
    #chatbot-button {
        position: fixed; bottom: 25px; right: 25px;
        width: 60px; height: 60px;
        background: linear-gradient(135deg, #4f46e5, #072b77);
        border-radius: 50%; color:white;
        display:flex; justify-content:center; align-items:center;
        font-size:28px; cursor:pointer; box-shadow:0 6px 18px rgba(0,0,0,0.25); z-index:9999;
        transition:all 0.3s ease;
    }
    #chatbot-button:hover { transform: scale(1.1); }
    #chatbot-window {
        position: fixed; bottom:95px; right:25px;
        width: 320px; height:400px;
        background: #fff; border-radius: 12px;
        box-shadow:0 12px 35px rgba(0,0,0,0.25);
        display:none; flex-direction: column; overflow:hidden; z-index:9999;
    }
    .chat-header {
        background: #001f63; color:white; padding:14px;
        font-weight:600; display:flex; justify-content:space-between; align-items:center;
    }
    .chat-header button {
        background: transparent; border:none; color:white; font-size:18px; cursor:pointer;
    }
    .chat-body { flex:1; padding:15px; overflow-y:auto; font-size:14px; background:#f3f4f6; }
    .chat-input { display:flex; border-top:1px solid #e5e7eb; }
    .chat-input input { flex:1; padding:12px; border:none; outline:none; font-size:14px; }
    .chat-input button {
        padding:12px 20px; background:#001f63; color:white; border:none; cursor:pointer; transition:0.2s;
    }
    .chat-input button:hover { background:#1e4ed8; transform:translateY(-1px); }
</style>
</head>
<body>

</style>
</head>
<body>

<div class="login-wrapper">
    <img src="assets/daysiders_logo.png" alt="Logo" class="logo">
    <h2 class="login-title">Sign in to your account</h2>
    <p class="login-subtext">Enter your credentials below to continue</p>

    <div id="error-msg" class="error-message"></div> <!-- Added for AJAX errors -->

    <form id="login-form" method="POST">
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
            </span>
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/></svg>
            </span>
            <input type="password" name="password" placeholder="Password" id="password" required>
            <span class="toggle-password" id="toggle-password">👁️</span>
        </div>

        <div class="remember-forgot">
            <label><input type="checkbox" name="remember"> Remember me</label>
            <a href="#">Forgot password?</a>
        </div>

        <button type="submit" class="login-btn">Sign In</button>
    </form>

    <div class="social-login-title">Or continue with</div>
    <button class="social-btn"><img src="https://www.svgrepo.com/show/475656/google-color.svg"> Google</button>
    <button class="social-btn"><img src="https://www.svgrepo.com/show/452062/microsoft.svg"> Microsoft</button>
</div>

<!-- Chat Widget -->
<div id="chatbot-container">
    <div id="chatbot-button">💬</div>
    <div id="chatbot-window">
        <div class="chat-header">
            <span>Contact Owner</span>
            <button id="chat-close">✖</button>
        </div>
        <div class="chat-body">
            <p><strong>Owner Bot:</strong> Hi! Ask me how to contact the owner.</p>
        </div>
        <div class="chat-input">
            <input type="text" id="chat-message" placeholder="Type your question...">
            <button id="chat-send">Send</button>
        </div>
    </div>
</div>

<script>
    // Password toggle
    const togglePwd = document.getElementById("toggle-password");
    const pwdField = document.getElementById("password");
    togglePwd.onclick = () => {
        if(pwdField.type==="password"){pwdField.type="text"; togglePwd.textContent="🙈";}
        else{pwdField.type="password"; togglePwd.textContent="👁️";}
    };

    // AJAX login
    const loginForm = document.getElementById("login-form");
    const errorMsg = document.getElementById("error-msg");

    loginForm.addEventListener("submit", function(e){
        e.preventDefault();
        errorMsg.style.display = "none";
        const formData = new FormData(loginForm);

        fetch('index.php', {  // submit to same file
            method:'POST',
            body: formData
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                if(data.role === 'admin'){
                    window.location.href = "admin/dashboard.php";
                } else {
                    window.location.href = "client/dashboard.php";
                }
            } else {
                errorMsg.textContent = data.message;
                errorMsg.style.display = "block";
                loginForm.classList.add("shake");
                setTimeout(()=>loginForm.classList.remove("shake"),400);
            }
        })
        .catch(err=>{
            console.error(err);
            errorMsg.textContent = "Server error, try again later.";
            errorMsg.style.display = "block";
        });
    });

    // Chat widget
    const chatBtn = document.getElementById("chatbot-button");
    const chatWindow = document.getElementById("chatbot-window");
    const chatClose = document.getElementById("chat-close");
    const chatSend = document.getElementById("chat-send");
    const chatMessage = document.getElementById("chat-message");
    const chatBody = document.querySelector(".chat-body");

    chatBtn.onclick = () => { chatWindow.style.display="flex"; chatBtn.style.display="none"; };
    chatClose.onclick = () => { chatWindow.style.display="none"; chatBtn.style.display="flex"; };
    chatSend.onclick = () => {
        const msg = chatMessage.value.trim();
        if(!msg) return;
        const userMsg = document.createElement("p");
        userMsg.innerHTML = `<strong>You:</strong> ${msg}`;
        chatBody.appendChild(userMsg);
        const botReply = document.createElement("p");
        botReply.innerHTML = `<strong>Owner Bot:</strong> You can contact the owner at <a href="mailto:owner@example.com">owner@example.com</a>`;
        botReply.style.marginTop="5px";
        chatBody.appendChild(botReply);
        chatMessage.value="";
        chatBody.scrollTop = chatBody.scrollHeight;
    };
</script>

</body>
</html>

