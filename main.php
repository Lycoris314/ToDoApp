<?php
require_once("helper_functions.php");
const MAX_TASK = 100;


$order = "";
$selected = "";

console_log($_COOKIE["order"]);

if (isset($_COOKIE["order"])) {
    $order = $_COOKIE["order"];
    if ($order == "priority") {
        $selected = "selected";
    }
}

$pdo = connect_db();

$total_task = total_task();



switch ($order) {
    case "":
    case "limit":
        $stmt_undone = $pdo->query("select * from task where done=0 order by time_limit ");
        $stmt_done = $pdo->query("select * from task where done=1 order by time_limit ");
        break;
    case "priority":
        $stmt_undone = $pdo->query("select * from task where done=0 order by priority desc, time_limit ");
        $stmt_done = $pdo->query("select * from task where done=1 order by priority desc, time_limit ");
        $selected = "selected";
        break;
}
$pdo = null;


//未完了と期限超過の振り分け
$in_time = [];
$over = [];

$now = time();
while ($row = $stmt_undone->fetch()) {

    $dt_obj = new Datetime($row[3]);
    $limit = $dt_obj->getTimestamp();

    if ($now <= $limit) {
        $in_time[] = $row;
    } else {
        $over[] = $row;
    }

}

//未完了、期限超過、完了タスクの総数
$in_time_num = count($in_time);
$over_num = count($over);
$done_num = $total_task - $in_time_num - $over_num;



//期限の時刻をリストへの表示形式に変える
function time_limit_show($datetime)
{
    $obj = new DateTime($datetime);
    $obj_now = new DateTime();
    $one_day = new DateInterval("P1D");

    if ($obj->format("Y-m-d") == $obj_now->format("Y-m-d")) {
        $datetime = "今日 " . $obj->format("H:i");

    } else if ($obj_now->add($one_day)->format("Y-m-d") == $obj->format("Y-m-d")) {
        $datetime = "明日 " . $obj->format("H:i");

    } else if ($obj_now->add($one_day)->format("Y-m-d") == $obj->format("Y-m-d")) {
        $datetime = "明後日 " . $obj->format("H:i");

        $obj_now = new DateTime(); //リセット
    } else if ($obj->format("Y") == $obj_now->format("Y")) {
        $datetime = $obj->format("m-d H:i");

    } else if ($datetime == "9999-12-31 23:59:59") {
        $datetime = "期限なし";
    }
    return $datetime;
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do App</title>
    <script src="jquery.js"></script>
    <script src="main.js"></script>
    <link rel="stylesheet" href="main.css">
</head>

<body>
    <header>
        <h1>To Do</h1>
        <div>
            <p class=Ymd></p>
            <p class=Hi></p>
        </div>
        <div>
            <p>タスク合計(
                <?= $total_task ?>/
                <?= MAX_TASK ?>)
            </p>
            <ul>
                <li><a href="#undone_section">未完了(
                        <?= $in_time_num ?> )
                    </a>
                </li>
                <li><a href="#over_section">期限超過(
                        <?= $over_num ?> )
                    </a>
                </li>
                <li><a href="#done_section">完了(<span class='done_num'>
                            <?= $done_num ?>
                        </span>)</a></li>
            </ul>
        </div>
        <select class="order">
            <option value="limit">期限順</option>
            <option value="priority" <?= $selected ?>>優先度順</option>
        </select>
    </header>
    <main>
        <section>
            <div id="undone_section"></div>
            <h2>未完了</h2>
            <?php if ($in_time_num == 0) {
                echo "<p>タスクなし</p>";
            } ?>
            <ul>
                <?php
                //$rowの内容： [id, content, priority, time_limit, done]
                foreach ($in_time as $row) {
                    $show = time_limit_show($row[3]);

                    $date = substr($row[3], 0, 10);
                    $time = substr($row[3], 11, 5);

                    echo "
                    <li data-priority='{$row[2]}' >
                        <p>
                            <input type='checkbox' class='checkbox' data-id='{$row[0]}'>

                            <span data-id='{$row[0]}' class='datetime' data-date={$date} data-time={$time}>{$show}</span>
                            
                            <button type='button' class='edit' data-id='{$row[0]}' data-priority='{$row[2]}'>編集</button>
                            
                            <a href='delete.php?id={$row[0]}'><button type='button'>削除</button></a>
                        </p>
                        <p data-id='{$row[0]}' class='content'>{$row[1]}</p>
                    </li>
                    ";
                }
                ?>
            </ul>
        </section>
        <hr>
        <section>
            <div id="over_section"></div>
            <h2>期限超過</h2>
            <?php if ($over_num == 0) {
                echo "<p>タスクなし</p>";
            } ?>
            <ul>
                <?php
                foreach ($over as $row) {
                    $show = time_limit_show($row[3]);

                    $date = substr($row[3], 0, 10);
                    $time = substr($row[3], 11, 5);

                    echo "
                    <li data-priority='{$row[2]}' >
                        <p>
                            <input type='checkbox' class='checkbox' data-id='{$row[0]}'>

                            <span data-id='{$row[0]}' class='datetime' data-date={$date} data-time={$time}>{$show}</span>
                            
                            <button type='button' class='edit' data-id='{$row[0]}' data-priority='{$row[2]}'>編集</button>
                            
                            <a href='delete.php?id={$row[0]}'><button type='button'>削除</button></a>
                        </p>
                        <p data-id='{$row[0]}' class='content'>{$row[1]}</p>
                    </li>
                    ";
                }
                ?>
            </ul>
        </section>
        <hr>
        <section>
            <div id="done_section"></div>
            <div class="h2_a">
                <h2>完了</h2>
                <a href="delete_done.php" class="delete_done"><button type="button">全て削除</button></a>
            </div>
            <?php if ($done_num == 0) {
                echo "<p>タスクなし</p>";
            } ?>
            <ul>
                <?php
                //$row = [id, content, priority, time_limit, done]
                while ($row = $stmt_done->fetch()) {

                    $show = time_limit_show($row[3]);

                    $date = substr($row[3], 0, 10);
                    $time = substr($row[3], 11, 5);

                    echo "
                    <li data-priority='{$row[2]}'>
                    <p>
                        <input type='checkbox' class='checkbox' data-id='{$row[0]}' checked>
                    
                        <span data-id='{$row[0]}' class='datetime' data-date={$date} data-time={$time}>{$show}</span>
                    
                        <button type='button' class='edit' data-id='{$row[0]}' data-priority='{$row[2]}'>編集</button>

                        <a href='delete.php?id={$row[0]}'><button type='button'>削除</button></a>
                    </p>
                    <p data-id='{$row[0]}' class='content'>{$row[1]}</p>
                    </li>
                    ";
                }
                ?>
            </ul>

        </section>
        <div class="open"></div>
    </main>


    <footer>
        <div class=close></div>
        <form id="footer_form">
            <div class="footer1">
                <div>
                    <label for="content">タスク内容</label>
                    <textarea name="content" id="content"></textarea>
                </div>

                <fieldset>
                    <legend>優先度</legend>
                    <ul>
                        <li>
                            <input type="radio" name="priority" value="2" id="high">
                            <label for="high">高</label>
                        </li>
                        <li>
                            <input type="radio" name="priority" value="1" id="middle" checked>
                            <label for="middle">中</label>
                        </li>
                        <li>
                            <input type="radio" name="priority" value="0" id="low">
                            <label for="low">低</label>
                        </li>
                    </ul>
                </fieldset>
            </div>
            <div class="footer2">
                <button class="new_post" data-total_task="<?= $total_task ?>">新規投稿</button>
                <div class="edit_post_buttons">
                    <button class="edit_post">編集投稿</button>
                    <button class="back" type="button">やめる</button>
                </div>
                <div>
                    <span>期限</span>
                    <?php $today = date("Y-m-d"); ?>
                    <input type="date" name="date" value=<?= $today ?> required>
                    <input type="number" name="hour" value="12" min="0" max="23">時
                    <input type="number" name="minute" value="0" min="0" max="59" step="5">分
                    <input type="checkbox" id="no_limit" name="no_limit">
                    <label for="no_limit">期限なし</label>
                </div>

            </div>
            <input type="hidden" name="id" class="hidden_id" value="">

        </form>
    </footer>
</body>

</html>