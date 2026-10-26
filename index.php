<?php
include('db.php');

// معرّف خاص بكل متصفح عشان نعرف رسائلك
if (empty($_COOKIE['chat_uid'])) {
    $uid = bin2hex(random_bytes(16));
    setcookie('chat_uid', $uid, time() + 60 * 60 * 24 * 365);
    $_COOKIE['chat_uid'] = $uid;
}
$uid = $_COOKIE['chat_uid'];

$error = '';

// تغيير الاسم: يمسح الاسم المحفوظ ويرجع حقل الاسم
if (isset($_GET['changename'])) {
    setcookie('chat_name', '', time() - 3600);
    header('location: index.php');
    exit;
}

$saved_name = trim($_COOKIE['chat_name'] ?? '');

if (isset($_POST['submit'])) {
    $n = $saved_name !== '' ? $saved_name : trim($_POST['name'] ?? '');
    $m = trim($_POST['message'] ?? '');

    if ($n === '' || $m === '') {
        $error = 'لازم تكتب اسمك ورسالتك قبل ما ترسل';
    } else {
        $stmt = mysqli_prepare($con, "insert into chat (name, message, owner) values (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $n, $m, $uid);
        mysqli_stmt_execute($stmt);

        // حفظ الاسم لمدة سنة
        setcookie('chat_name', $n, time() + 60 * 60 * 24 * 365);

        header('location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>faisal's chatapp</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // لما الماوس فوق زر الحذف نوقف التحديث عشان ما يضيع الضغطة
        var overDel = false;
        document.addEventListener('mouseover', function(e){
            overDel = !!(e.target.closest && e.target.closest('.del-btn'));
        });

        function aj(){
            if(overDel) return;
            var req = new XMLHttpRequest();
            req.onreadystatechange = function(){
                if(req.readyState==4 && req.status==200){
                    document.getElementById('chat').innerHTML= req.responseText;
                }
            }
        req.open('GET', 'chat.php',true);
        req.send();
        }
        setInterval(function(){aj()},4000);

        function del(id){
            if(!confirm('Delete this message?')) return;
            var req = new XMLHttpRequest();
            req.onload = function(){
                if(req.status !== 200){
                    alert('Delete failed: HTTP ' + req.status + ' (is delete.php in the same folder as index.php?)');
                } else if(req.responseText.trim() !== 'ok'){
                    alert(req.responseText);
                }
                overDel = false;
                aj();
            };
            req.onerror = function(){ alert('Delete failed: could not reach delete.php'); };
            req.open('POST', 'delete.php', true);
            req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            req.send('id=' + id);
        }

        // Enter يرسل، Shift+Enter سطر جديد، وحقل الكتابة يكبر مع النص
        document.addEventListener('DOMContentLoaded', function(){
            var form = document.getElementById('chatform');
            var box = form.querySelector('textarea[name=message]');
            var sendBtn = form.querySelector('button[name=submit]');
            box.addEventListener('input', function(){
                box.style.height = '48px';
                box.style.height = Math.min(box.scrollHeight + 2, 120) + 'px';
            });
            box.addEventListener('keydown', function(e){
                if(e.key === 'Enter' && !e.shiftKey && !e.isComposing){
                    e.preventDefault();
                    form.requestSubmit(sendBtn);
                }
            });
        });
    </script>
</head>
<body onload="aj();">
    <div class="container">
        <div class="header">
            <h1>faisal's chatapp</h1>
            <p>Messages are deleted after 5 hours</p>
        </div>

        <div class="chatbox">
            <div id="chat"></div>
        </div>

        <div class="composer-wrap">
            <?php if ($error !== '') { ?>
                <p class="error" role="alert"><?php echo $error; ?></p>
            <?php } ?>
            <form action="index.php" method="post" id="chatform">
                <?php if ($saved_name === '') { ?>
                    <input class="field name-field" type="text" name="name" placeholder="Your name" required autocomplete="nickname" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                <?php } else { ?>
                    <p class="whoami">Chatting as <strong><?php echo htmlspecialchars($saved_name); ?></strong><a href="index.php?changename=1">Change name</a></p>
                <?php } ?>
                <div class="composer">
                    <textarea class="field message-field" name="message" placeholder="Write a message" rows="1" required></textarea>
                    <button class="send" type="submit" name="submit" value="Send">Send</button>
                </div>
                <p class="hint">Press Enter to send. Shift+Enter adds a new line.</p>
            </form>
        </div>
    </div>
</body>
</html>