$(() => {

    //開始時にテキストエリアにフォーカス
    $("textarea").focus();

    $("form").on("submit", (e) => {
        e.preventDefault();

        const parameter = $("form").serialize();

        if ($("textarea").val() == "") {
            alert("タスクの内容を入力してください");
        } else {

            //新規投稿
            if ($(".new_post").css("display") == "block") {

                if ($(".new_post").attr("data-total_task") >= 100) {
                    alert("タスクの登録上限100件に達しています。");
                } else {

                    my_ajax("form.php", "post", parameter)
                        .done((data) => {

                            if (data == "success") {
                                location.assign("main.php");
                            } else {
                                alert("投稿に失敗しました。");
                            }
                        }).fail(() => {

                            alert("投稿に失敗しました。");
                        });
                }

                //編集投稿
            } else if ($(".edit_post").css("display") == "block") {

                my_ajax("edit.php", "post", parameter)
                    .done((data) => {

                        if (data == "success") {
                            location.assign("main.php");
                        } else {
                            alert("更新に失敗しました。");
                        }
                    }).fail(() => {
                        alert("更新に失敗しました。")
                    });
            }
        }
    });


    //チェックボックス
    $(".checkbox").on("click", function () {
        let id = $(this).attr('data-id');

        my_ajax("checkbox.php", "get", "id=" + id)

            .done((data) => {
                if (data == "success") {
                    move_li($(this));
                } else {
                    alert("更新に失敗しました。");
                }
            }).fail(() => {
                alert("更新に失敗しました。");
            })

        function move_li(obj) {

            let done = obj.attr("data-done");

            obj.attr("data-done", 1 - Number(done));

            let li = $(`li[data-id=${id}]`);

            if (done == "0") {
                $("ul.done").prepend(li);
            } else {
                //現在時刻と比較して移動先を決める
                if (Date.now() <= Date.parse(obj.attr("data-time_limit"))) {
                    $("ul.in_time").prepend(li);
                } else {
                    $("ul.over").prepend(li);
                }
            }
        }

    });


    //編集ボタンをクリック
    $("button.edit").on("click", function () {

        $("textarea").focus();

        $(".new_post").css("display", "none");
        $(".edit_post").css("display", "block");
        $(".back").css("display", "block");
        $(".edit_post_buttons").css("display", "flex");

        $("footer").css("bottom", "0");

        const id = $(this).attr("data-id");

        const datee = $(`span[data-id=${id}]`).attr("data-date");
        const time = $(`span[data-id=${id}]`).attr("data-time");
        const hour = time.slice(0, 2);
        const minute = time.slice(3, 5);
        const content = $(`p[data-id=${id}]`).text();
        const no_limit = $(this).attr("data-no_limit");

        $(".hidden_id").attr("value", id);//編集するタスクのidを記録しておく。

        $("textarea").val(content);

        switch (no_limit) {
            case "0":

                $("input[type='date']").val(datee);
                $("input[name='hour']").val(hour);
                $("input[name='minute']").val(minute);
                break;
            case "1":
                //デフォルト状態
                let date = new Date();
                let Year = date.getFullYear();
                let month = date.getMonth() + 1;
                let date_ = date.getDate();
                let Ymd = Year + "-" + append_0(month) + "-" + append_0(date_);

                $("input[type='date']").val(Ymd);
                $("input[name='hour']").val(12);
                $("input[name='minute']").val(0);
                break;
        }


        const priority = $(this).attr("data-priority");

        $(`input[name=priority][value=${priority}]`).prop("checked", true);

        $("#no_limit").prop("checked", "");
        $("input[type=date]").attr("disabled", false);
        $("input[type=number]").attr("disabled", false);
        if (no_limit == 1) {
            $("#no_limit").prop("checked", true);
            $("input[type=date]").attr("disabled", true);
            $("input[type=number]").attr("disabled", true);

        }
    });

    //削除ボタン
    $("button.delete").on("click", function () {
        if (confirm("削除してもよろしいですか")) {

            let id = $(this).attr("data-id");

            my_ajax("delete.php", "get", "id=" + id)
                .done((data) => {
                    if (data = "success") {
                        const li = $(`li[data-id=${id}]`);
                        li.remove();

                        //ヘッダのタスク数の変更
                        let total_task = Number($(".total_task").text()) - 1;
                        $(".total_task").text(total_task);

                        let obj;
                        switch (li.attr("data-type")) {
                            case "in_time":
                                obj = $(".in_time_num");
                                break;
                            case "over":
                                obj = $(".over_num");
                                break;
                            case "done":
                                obj = $(".done_num");
                                break;
                        }
                        let n = Number(obj.text()) - 1;
                        obj.text(n);

                    } else {
                        alert("削除に失敗しました");
                    }
                }).fail(() => {
                    alert("削除に失敗しました。")
                });
        }
    });

    //全て削除ボタン
    $("a.delete_done").on("click", (e) => {

        if ($(".done_num").text() == 0) {
            alert("完了したタスクがありません。");
            e.preventDefault();
        } else
            if (!confirm("完了タスクをすべて削除してもよろしいですか")) {
                e.preventDefault();
            }
    });

    //やめるボタン
    $(".back").on("click", () => {
        $(".new_post").css("display", "block");
        $(".edit_post").css("display", "none");
        $(".back").css("display", "none");
        $(".edit_post_buttons").css("display", "none");
        $("textarea").val("");
        $("input[name=hour]").val(12);
        $("input[name=minute]").val(0);

        let date = new Date();
        let Year = date.getFullYear();
        let month = date.getMonth() + 1;
        let date_ = date.getDate();
        let Ymd = Year + "-" + append_0(month) + "-" + append_0(date_);

        $("input[type='date']").val(Ymd);
        $("#no_limit").prop("checked", "");
        $("input[type=date]").attr("disabled", false);
        $("input[type=number]").attr("disabled", false);
        $(`input[name=priority][value=1]`).prop("checked", true);
    })


    //期限なしにチェック
    $("#no_limit").on("click", function () {

        if ($(this).prop("checked") == true) {

            $("input[type=number]").prop("disabled", "true");
            $("input[type=date]").prop("disabled", "true");

        } else {
            $("input[type=number]").prop("disabled", "");
            $("input[type=date]").prop("disabled", "");


        }
    })

    //ソート方法の変更
    $(".order").on("change", function () {

        const val = $(this).prop("value");

        location.assign("order.php?order=" + val);
    })

    //通常モードか残存モードか
    $(".show_mode").on("change", function () {

        const val = $(this).prop("value");

        location.assign("show_mode.php?show_mode=" + val);
    })

    //時計機能
    function timer() {
        let date = new Date();
        let Year = date.getFullYear();
        let month = date.getMonth() + 1;
        let date_ = date.getDate();
        let Ymd = Year + "-" + month + "-" + date_;
        $(".Ymd").text(Ymd);

        let hour = date.getHours();
        hour = append_0(hour);

        let minute = date.getMinutes();
        minute = append_0(minute);

        let Hi = hour + ":" + minute;
        $(".Hi").text(Hi);
    }
    timer();
    setInterval(timer, 1000);

    function append_0(str) {
        str = "0" + str;
        str = str.slice(-2);
        return str;
    }

    //footerの出し入れ
    $(".close").on("click", () => {
        $("footer").css("bottom", "-170px");

        my_ajax("footer.php", "get", "footer=close")
    });

    $(".open").on("click", () => {
        $("footer").css("bottom", "0");
        $("textarea").focus();

        my_ajax("footer.php", "get", "footer=open")
    });

});


function my_ajax(url, type, data) {

    const r =
        $.ajax({
            url: url,
            type: type,
            data: data,
            cache: false,
        });
    return r;
};


