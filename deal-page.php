<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$deal_id = $_GET['id'] ?? '';

if (empty($deal_id)) {
    header("Location: dashboard.php");
    exit();
}

// Get deal information and verify access
try {
    $stmt = $pdo->prepare("
        SELECT d.*, 
               p.title as project_title,
               p.description as project_description,
               p.budget_min, p.budget_max,
               pr.bid_amount, pr.delivery_time,
               c.first_name as client_first_name, c.last_name as client_last_name,
               f.first_name as freelancer_first_name, f.last_name as freelancer_last_name
        FROM deals d
        LEFT JOIN proposals pr ON d.proposal_id = pr.id
        LEFT JOIN projects p ON pr.project_id = p.id
        LEFT JOIN users c ON d.client_id = c.id
        LEFT JOIN users f ON d.freelancer_id = f.id
        WHERE d.id = ? AND (d.client_id = ? OR d.freelancer_id = ?)
    ");
    $stmt->execute([$deal_id, $user_id, $user_id]);
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deal) {
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: dashboard.php");
    exit();
}

// Handle deal status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'] ?? '';
    if (in_array($new_status, ['ongoing', 'completed', 'cancelled'])) {
        try {
            $stmt = $pdo->prepare("UPDATE deals SET status = ? WHERE id = ? AND (client_id = ? OR freelancer_id = ?)");
            $stmt->execute([$new_status, $deal_id, $user_id, $user_id]);
            $deal['status'] = $new_status;
        } catch (PDOException $e) {
            // Handle error silently
        }
    }
}

// Get messages for this deal
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.first_name, u.last_name, u.user_type
        FROM messages m
        LEFT JOIN users u ON m.sender_id = u.id
        WHERE m.deal_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$deal_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $messages = [];
}

// Mark messages as seen
try {
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET seen = 1 
        WHERE deal_id = ? AND sender_id != ?
    ");
    $stmt->execute([$deal_id, $user_id]);
} catch (PDOException $e) {
    // Handle error silently
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal - <?php echo htmlspecialchars($deal['project_title']); ?> - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/proposals.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="deal-container">
        <div class="deal-header">
            <h1 class="deal-title"><?php echo htmlspecialchars($deal['project_title']); ?></h1>
            <p class="deal-subtitle">
                Deal between
                <strong><?php echo htmlspecialchars($deal['client_first_name'] . ' ' . $deal['client_last_name']); ?></strong>
                and
                <strong><?php echo htmlspecialchars($deal['freelancer_first_name'] . ' ' . $deal['freelancer_last_name']); ?></strong>
            </p>

            <div class="deal-info">
                <div class="detail-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Budget: $<?php echo number_format($deal['bid_amount']); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span>Delivery: <?php echo $deal['delivery_time']; ?> days</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-calendar"></i>
                    <span>Started: <?php echo date('M j, Y', strtotime($deal['created_at'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="deal-status status-<?php echo $deal['status']; ?>">
                        <?php echo ucfirst($deal['status']); ?>
                    </span>
                </div>
            </div>

            <?php if ($deal['status'] === 'ongoing'): ?>
                <div style="margin-top: 1rem;">
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="action-btn btn-accept"
                            onclick="return confirm('Mark this deal as completed?')">
                            <i class="fas fa-check"></i> Mark as Completed
                        </button>
                    </form>

                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="action-btn btn-reject"
                            onclick="return confirm('Cancel this deal? This action cannot be undone.')">
                            <i class="fas fa-times"></i> Cancel Deal
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Chat Interface -->
        <div class="chat-container">
            <div class="chat-header">
                <div class="chat-title">Project Chat</div>
                <div class="chat-status">
                    <span id="connection-status">Connected</span>
                </div>
            </div>

            <div class="messages-container" id="messages-container">
                <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo $message['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                        <div class="message-bubble">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>

                            <?php if (!empty($message['file'])): ?>
                                <div class="message-file">
                                    <i class="fas fa-paperclip"></i>
                                    <a href="<?php echo htmlspecialchars($message['file']); ?>" target="_blank">
                                        View Attachment
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="message-time">
                            <?php echo date('M j, g:i A', strtotime($message['created_at'])); ?>
                            <?php if ($message['sender_id'] == $user_id && $message['seen']): ?>
                                <div class="message-seen">✓ Seen</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chat-input-container">
                <form class="chat-input-form" id="chat-form" enctype="multipart/form-data">
                    <input type="hidden" name="deal_id" value="<?php echo $deal_id; ?>">

                    <div class="message-input-group">
                        <textarea name="message" class="message-input" placeholder="Type your message here..."
                            required></textarea>
                    </div>

                    <div class="file-upload">
                        <input type="file" id="file-upload" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                        <button type="button" class="file-upload-btn"
                            onclick="document.getElementById('file-upload').click()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                    </div>

                    <button type="submit" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        const dealId = <?php echo $deal_id; ?>;
        const userId = <?php echo $user_id; ?>;
        const messagesContainer = document.getElementById('messages-container');
        const chatForm = document.getElementById('chat-form');
        const messageInput = chatForm.querySelector('textarea[name="message"]');
        const fileUpload = document.getElementById('file-upload');
        const sendBtn = chatForm.querySelector('.send-btn');

        // Auto-scroll to bottom
        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Send message
        chatForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const message = messageInput.value.trim();

            if (!message) return;

            // Disable send button
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="loading"></span>';

            try {
                const response = await fetch('send-message.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    messageInput.value = '';
                    fileUpload.value = '';
                    await loadMessages();
                } else {
                    alert('Failed to send message: ' + result.error);
                }
            } catch (error) {
                alert('Failed to send message. Please try again.');
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
            }
        });

        // Load messages
        async function loadMessages() {
            try {
                const response = await fetch(`get-messages.php?deal_id=${dealId}`);
                const result = await response.json();

                if (result.success) {
                    messagesContainer.innerHTML = result.messages.map(message => {
                        const isSent = message.sender_id == userId;
                        const messageTime = new Date(message.created_at).toLocaleString();

                        return `
                            <div class="message ${isSent ? 'sent' : 'received'}">
                                <div class="message-bubble">
                                    ${message.message.replace(/\n/g, '<br>')}
                                    ${message.file ? `
                                        <div class="message-file">
                                            <i class="fas fa-paperclip"></i>
                                            <a href="${message.file}" target="_blank">View Attachment</a>
                                        </div>
                                    ` : ''}
                                </div>
                                <div class="message-time">
                                    ${messageTime}
                                    ${isSent && message.seen ? '<div class="message-seen">✓ Seen</div>' : ''}
                                </div>
                            </div>
                        `;
                    }).join('');

                    scrollToBottom();
                }
            } catch (error) {
                console.error('Failed to load messages:', error);
            }
        }

        // Auto-refresh messages every 5 seconds
        setInterval(loadMessages, 5000);

        // Initial scroll to bottom
        scrollToBottom();

        // File upload preview
        fileUpload.addEventListener('change', function () {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                console.log('File selected:', fileName);
            }
        });

        // Enter key to send (Shift+Enter for new line)
        messageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>

</html>