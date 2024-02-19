<?php
require_once("helper_functions.php");
const MAX_STRLEN = 500;


//「期限なし」なら１にする。
$no_limit = 0;
if (isset($_POST["no_limit"])) {
    $no_limit = 1;
}


$content = is_set("post","content");
$priority = is_set("post","priority");
$id = is_set("post","id");
if (
    !(
        $content !="" &&
        mb_strlen($content, "utf-8") < MAX_STRLEN &&
        preg_match('|^[012]$|', $priority) &&
        preg_match('|^[0-9]+$|', $id)
    )
) {
    echo "パラメータのパターンが不正です。";
    exit();
}


if ($no_limit == 0) {

    $date = is_set("post", "date");
    $hour = is_set("post", "hour");
    $minute = is_set("post", "minute");
    if (
        !(
            preg_match('|^[0-9]{4}-[0-9]{2}-[0-9]{2}$|', $date) &&
            preg_match('|^[0-9]{1,2}$|', $hour) &&
            preg_match('|^[0-9]{1,2}$|', $minute)
        )
    ) {
        echo "パラメータのパターンが不正です。";
        exit();
    }
}


$time_limit = match ($no_limit) {
    1 => "9999-12-31 23:59:59", //「期限なし」における期限
    0 => "{$date} {$hour}:{$minute}:00"
};

//エスケープ処理
$content = h($content);

//ハイパーリンク機能
$content =
    preg_replace_callback(
        "|https?://[\w!?/+\-~:=;.,*&@#$%()'[\]]+|",

        function ($m) {
            $decoded = urldecode($m[0]);
            return "<a href={$m[0]}>{$decoded}</a>";
        },
        $content
    );

//改行処理
$content = str_replace(PHP_EOL, "<br>", $content);

//登録
$pdo = connect_db();
upd_sql($pdo, "update task set content=?, priority=?, time_limit=?, no_limit=? where id=?", $content, $priority, $time_limit, $no_limit, $id);
$pdo = null;
echo "success";