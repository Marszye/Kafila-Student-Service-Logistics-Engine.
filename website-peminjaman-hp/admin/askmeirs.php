<?php
session_start();
include '../peminjaman/config.php'; // 包含配置文件

// 设置 OpenAI API 密钥
$openai_api_key = 'sk-abcdefabcdefabcdefabcdefabcdefabcdef12';

// 处理聊天机器人请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_message'])) {
    $user_message = $_POST['user_message'];
    
    // 调用 OpenAI API
    $response = openai_chat($user_message, $openai_api_key);
    
    // 保存聊天记录到数据库
    save_chat_history($user_message, $response);
    
    // 返回 JSON 响应
    echo json_encode(['response' => $response]);
    exit;
}

// 保存聊天记录到数据库
function save_chat_history($user_message, $ai_response) {
    global $conn;
    try {
        $sql = "INSERT INTO chat_history (user_message, ai_response, created_at) 
                VALUES (:user_message, :ai_response, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_message' => $user_message,
            ':ai_response' => $ai_response
        ]);
    } catch (Exception $e) {
        error_log("Error saving chat history: " . $e->getMessage());
    }
}

// 调用 OpenAI API
function openai_chat($message, $api_key) {
    $url = 'https://api.openai.com/v1/chat/completions';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ];
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant for a boarding school management system.'],
            ['role' => 'user', 'content' => $message]
        ],
        'temperature' => 0.7,
        'max_tokens' => 150
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $response_data = json_decode($response, true);
    return $response_data['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask Me (IRS) - Pop-up Chat</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .chat-toggle-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background-color: #333;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        
        .chatbot-container {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 350px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transform: translateY(100%);
            transition: transform 0.3s ease-out;
            z-index: 900;
        }
        
        .chatbot-container.active {
            transform: translateY(0);
        }
        
        .chat-header {
            background-color: #333;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-body {
            height: 400px;
            padding: 15px;
            overflow-y: auto;
        }
        
        .chat-bubble-user {
            background-color: #000;
            color: white;
            padding: 10px 15px;
            border-radius: 15px 15px 0 15px;
            max-width: 70%;
            margin-bottom: 10px;
            display: inline-block;
            position: relative;
            float: right;
            clear: both;
        }
        
        .chat-bubble-user::after {
            content: '';
            position: absolute;
            right: -5px;
            top: 10px;
            border-width: 5px 5px 5px 0;
            border-style: solid;
            border-color: transparent #000 transparent transparent;
        }
        
        .chat-bubble-ai {
            background-color: #f1f1f1;
            color: black;
            padding: 10px 15px;
            border-radius: 15px 15px 15px 0;
            max-width: 70%;
            margin-bottom: 10px;
            display: inline-block;
            position: relative;
            float: left;
            clear: both;
        }
        
        .chat-bubble-ai::after {
            content: '';
            position: absolute;
            left: -5px;
            top: 10px;
            border-width: 5px 0 5px 5px;
            border-style: solid;
            border-color: transparent transparent transparent #f1f1f1;
        }
        
        .chat-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .chat-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            margin-right: 10px;
        }
        
        .send-button {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Chatbot Toggle Button -->
    <div class="chat-toggle-btn" id="chatToggle">
        <i class="fas fa-comment"></i>
    </div>
    
    <!-- Chatbot Container -->
    <div class="chatbot-container" id="chatContainer">
        <div class="chat-header">
            <h3>Ask Me (IRS)</h3>
            <button id="closeChat" class="text-white">&times;</button>
        </div>
        <div class="chat-body" id="chatBody">
            <!-- 初始消息 -->
            <div class="chat-bubble-ai">
                Halo! Bagaimana saya bisa membantu Anda hari ini?
            </div>
        </div>
        <div class="chat-footer">
            <input type="text" class="chat-input" id="chatInput" placeholder="Tulis pesan Anda...">
            <button id="sendButton" class="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script>
        // Chatbot toggle functionality
        const chatContainer = document.getElementById('chatContainer');
        const chatToggle = document.getElementById('chatToggle');
        const closeChat = document.getElementById('closeChat');
        const sendButton = document.getElementById('sendButton');
        const chatInput = document.getElementById('chatInput');
        const chatBody = document.getElementById('chatBody');
        
        chatToggle.addEventListener('click', () => {
            chatContainer.classList.add('active');
        });
        
        closeChat.addEventListener('click', () => {
            chatContainer.classList.remove('active');
        });
        
        // Send message functionality
        sendButton.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
        
        function sendMessage() {
            const userMessage = chatInput.value.trim();
            if (userMessage === '') return;
            
            // 添加用户消息
            const userBubble = document.createElement('div');
            userBubble.className = 'chat-bubble-user';
            userBubble.textContent = userMessage;
            chatBody.appendChild(userBubble);
            
            // 清空输入框
            chatInput.value = '';
            
            // 滚动到底部
            chatBody.scrollTop = chatBody.scrollHeight;
            
            // 发送请求到服务器
            fetch('askmeirs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_message=${encodeURIComponent(userMessage)}`
            })
            .then(response => response.json())
            .then(data => {
                // 添加AI回复
                const aiBubble = document.createElement('div');
                aiBubble.className = 'chat-bubble-ai';
                aiBubble.textContent = data.response;
                chatBody.appendChild(aiBubble);
                
                // 滚动到底部
                chatBody.scrollTop = chatBody.scrollHeight;
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsgDiv = document.createElement('div');
                errorMsgDiv.className = 'chat-bubble-ai';
                errorMsgDiv.textContent = 'Maaf, terjadi kesalahan. Silakan coba lagi.';
                chatBody.appendChild(errorMsgDiv);
                
                // 滚动到底部
                chatBody.scrollTop = chatBody.scrollHeight;
            });
        }
    </script>
</body>
</html>