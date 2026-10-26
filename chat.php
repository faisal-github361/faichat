<?php
include('db.php');
$uid = $_COOKIE['chat_uid'] ?? '';
mysqli_query($con, "DELETE FROM chat WHERE date < (NOW() - INTERVAL 5 HOUR)");
$query='select * from chat ORDER BY id DESC';
$run=mysqli_query($con, $query);

if (mysqli_num_rows($run) === 0) {
    echo '<div class="empty">No messages yet. Write the first one below.</div>';
}

while($row=mysqli_fetch_array($run)){
    $id = (int)$row['id'];
    $rawName = trim((string)$row['name']);
    if ($rawName === '') { $rawName = '?'; }
    $name = htmlspecialchars($rawName, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars((string)$row['message'], ENT_QUOTES, 'UTF-8');
    $full_date = htmlspecialchars((string)$row['date'], ENT_QUOTES, 'UTF-8');
    $ts = strtotime((string)$row['date']);
    $time = $ts ? date('H:i', $ts) : '';
    $initial = function_exists('mb_substr')
        ? mb_strtoupper(mb_substr($rawName, 0, 1, 'UTF-8'), 'UTF-8')
        : strtoupper(substr($rawName, 0, 1));
    $initial = htmlspecialchars($initial, ENT_QUOTES, 'UTF-8');
    $hue = abs(crc32($rawName)) % 360;
    $is_mine = ($uid !== '' && ($row['owner'] ?? '') === $uid);

    if ($is_mine) {
?>
<div class="row mine">
    <div class="bubble">
        <div class="text" dir="auto"><?php echo $message; ?></div>
        <div class="foot">
            <span class="time" title="<?php echo $full_date; ?>"><?php echo $time; ?></span>
            <button type="button" class="del-btn" onclick="del(<?php echo $id; ?>)" aria-label="Delete message" title="Delete">&#10005;</button>
        </div>
    </div>
</div>
<?php } else { ?>
<div class="row other">
    <div class="avatar" style="background: hsl(<?php echo $hue; ?>, 42%, 36%);"><?php echo $initial; ?></div>
    <div class="bubble">
        <div class="name" dir="auto" style="color: hsl(<?php echo $hue; ?>, 45%, 28%);"><?php echo $name; ?></div>
        <div class="text" dir="auto"><?php echo $message; ?></div>
        <div class="foot">
            <span class="time" title="<?php echo $full_date; ?>"><?php echo $time; ?></span>
        </div>
    </div>
</div>
<?php
    }
}
?>