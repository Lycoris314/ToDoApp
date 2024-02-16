<?php
require_once("helper_functions.php");
const MAX_TASK = 100;

//保存しておいた並べる順序
$order = "";
$selected = "";

if (isset($_COOKIE["order"])) {
    $order = $_COOKIE["order"];
    if ($order == "priority") {
        $selected = "selected";
    }
}

//保存しておいた表示モード
$show_mode = "";
$selected_mode = "";

if (isset($_COOKIE["show_mode"])) {
    $show_mode = $_COOKIE["show_mode"];
    if ($show_mode == "remaining") {
        $selected_mode = "selected";
    }
}



$pdo = connect_db();

//総タスク数
$stmt = ref_sql("select count(id) from task");
$total_task = $stmt->fetchColumn();


switch ($order) {
    case "":
    case "limit":
        $stmt_undone = ref_sql("select * from task where done=0 order by time_limit ");
        $stmt_done = ref_sql("select * from task where done=1 order by time_limit ");
        break;
    case "priority":
        $stmt_undone = ref_sql("select * from task where done=0 order by priority desc, time_limit ");
        $stmt_done = ref_sql("select * from task where done=1 order by priority desc, time_limit ");
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
$time_limit_show = function ($datetime) {
    $obj = new DateTime($datetime);
    $obj_now = new DateTime();
    $one_day = new DateInterval("P1D");

    if ($obj->format("Y-m-d") == $obj_now->format("Y-m-d")) {
        return "今日 " . $obj->format("H:i");
    }
    if ($obj_now->add($one_day)->format("Y-m-d") == $obj->format("Y-m-d")) {
        return "明日 " . $obj->format("H:i");
    }
    if ($obj_now->add($one_day)->format("Y-m-d") == $obj->format("Y-m-d")) {
        return "明後日 " . $obj->format("H:i");
    }
    $obj_now = new DateTime(); //リセット

    if ($obj->format("Y") == $obj_now->format("Y")) {
        return $obj->format("m-d H:i");
    }
    if ($datetime == "9999-12-31 23:59:59") {
        return "期限なし";
    }
    return $obj->format("Y-m-d H:i");
};

//残存モードでの表示
$interval_show = function ($datetime) {
    if ($datetime == "9999-12-31 23:59:59") {
        return "期限なし";
    }
    $obj = new DateTime($datetime);
    $obj_now = new DateTime();

    $interval = $obj_now->diff($obj);

    if ($interval->format("%a%h") == "00") {
        return $interval->format("あと%i分");
    }
    if ($interval->format("%a") == "0") {
        return $interval->format("あと%h時間 %i分");
    }
    return $interval->format("あと%a日と%h時間 %i分");
}

    ?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do App</title>
    <script src="jquery.js"></script>

    <!-- 保存しておいたフッターの表示／非表示 -->
    <?php
    if (isset($_COOKIE["footer"]) && $_COOKIE["footer"] == "close") {

        echo "<script src='footer.js'></script>";
    }
    ?>

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
                <span class="total_task">
                    <?= $total_task ?>
                </span>/
                <?= MAX_TASK ?>)
            </p>
            <ul>
                <li><a href="#undone_section">未完了(
                        <span class="in_time_num">
                            <?= $in_time_num ?>
                        </span>
                        )</a>
                </li>
                <li><a href="#over_section">期限超過(
                        <span class="over_num">
                            <?= $over_num ?>
                        </span>
                        )</a>
                </li>
                <li><a href="#done_section">完了(
                        <span class='done_num'>
                            <?= $done_num ?>
                        </span>
                        )</a>
                </li>
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
            <div class="h2_a">
                <h2>未完了</h2>
                <select class="show_mode">
                    <option value="normal">期限モード</option>
                    <option value="remaining" <?= $selected_mode ?>>残存モード</option>
                </select>
            </div>
            <?php if ($in_time_num == 0) {
                echo "<p>タスクなし</p>";
            } ?>

            <ul class="in_time">
                <?php
                $f = $time_limit_show;
                if ($show_mode == "remaining") {
                    $f = $interval_show;
                }

                //$rowの内容： [id, content, priority, time_limit, done, $no_limit]
                foreach ($in_time as $row) {
                    $show = $f($row[3]);

                    $date = substr($row[3], 0, 10);
                    $time = substr($row[3], 11, 5);

                    echo "
                    <li data-priority='{$row[2]}' data-id='{$row[0]}' data-type='in_time'>
                        <p>
                            <input type='checkbox' class='checkbox' data-id='{$row[0]}' data-done='0' data-time_limit='{$row[3]}' data-type='in_time'>

                            <span data-id='{$row[0]}' class='datetime' data-date={$date} data-time={$time}>{$show}</span>
                            
                            <button type='button' class='edit' data-id='{$row[0]}' data-priority='{$row[2]}' data-no_limit='{$row[5]}'>編集</button>
                            
                            <button type='button' class='delete' data-id='{$row[0]}'>削除</button>
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

            <ul class="over">
                <?php
                foreach ($over as $row) {
                    $show = $time_limit_show($row[3]);

                    $date = substr($row[3], 0, 10);
                    $time = substr($row[3], 11, 5);

                    echo "
                    <li data-priority='{$row[2]}' data-id='{$row[0]}' data-type='over'>
                        <p>
                            <input type='checkbox' class='checkbox' data-id='{$row[0]}' data-done='0' data-time_limit='{$row[3]}' data-type='over'>

                            <span data-id='{$row[0]}' class='datetime' data-date={$date} data-time={$time}>{$show}</span>
                            
                            <button type='button' class='edit' data-id='{$row[0]}' data-priority='{$row[2]}' data-no_limit='{$row[5]}'>編集</button>
                            
                            <button type='button' class='delete' data-id='{$row[0]}'>削除</button>
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

            <ul class="done">
                <?php
                //$row = [id, content, priority, time_limit, done]
                while ($row = $stmt_done->fetch()) {

                    $show = $time_limit_show($row[3]);

                    $date = substr($row[3], 0, 10);
                    $time = substr($row[3], 11, 5);

                    echo "
                    <li data-priority='{$row[2]}'data-id='{$row[0]}' data-type='done'>
                        <p>
                            <input type='checkbox' class='checkbox' data-id='{$row[0]}' data-done='1' data-time_limit='{$row[3]}' checked>
                        
                            <span data-id='{$row[0]}' class='datetime' data-date={$date} data-time={$time}>{$show}</span>
                        
                            <button type='button' class='edit' data-id='{$row[0]}' data-priority='{$row[2]}' data-no_limit='{$row[5]}'>編集</button>

                            <button type='button' class='delete' data-id='{$row[0]}'>削除</button>
                        </p>
                        <p data-id='{$row[0]}' class='content'>{$row[1]}</p>
                    </li>
                    ";
                }
                ?>
            </ul>

        </section>
        <div class="open">
            <div class="open_icon"></div>
        </div>
    </main>


    <footer class="footer-open">
        <div class="close">
            <div class="close_icon"></div>
        </div>

        <form id="footer_form">
            <div class="footer1">
                <div>
                    <label for="content">タスク内容</label>
                    <textarea name="content" id="content" maxlength="499" tabindex="1"></textarea>
                </div>

                <fieldset>
                    <legend>優先度</legend>
                    <ul>
                        <li>
                            <input type="radio" name="priority" value="2" tabindex="6" id="high">
                            <label for="high">高</label>
                        </li>
                        <li>
                            <input type="radio" name="priority" value="1" id="middle" checked tabindex="6">
                            <label for="middle">中</label>
                        </li>
                        <li>
                            <input type="radio" name="priority" value="0" id="low" tabindex="6">
                            <label for="low">低</label>
                        </li>
                    </ul>
                </fieldset>
            </div>

            <div class="footer2">
                <button class="new_post new_post_show" data-total_task="<?= $total_task ?>" tabindex="7">新規投稿</button>

                <div class="edit_post_buttons edit_post_buttons_hidden">
                    <button class="edit_post" tabindex="7">編集投稿</button>
                    <button class="back" type="button" tabindex="8">やめる</button>
                </div>
                <div>
                    <span>期限</span>
                    <?php $today = date("Y-m-d"); ?>
                    <input type="date" name="date" value=<?= $today ?> tabindex="2" required>
                    <input type="number" name="hour" value="12" min="0" max="23" tabindex="3">時
                    <input type="number" name="minute" value="0" min="0" max="59" step="5" tabindex="4">分

                    <input type="checkbox" id="no_limit" name="no_limit" tabindex="5">
                    <label for="no_limit">期限なし</label>
                </div>

            </div>

            <input type="hidden" name="id" class="hidden_id" value="">

        </form>
    </footer>
</body>

</html>