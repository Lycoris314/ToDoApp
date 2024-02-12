$(() => {
    console.log("更新確認12");

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

                    $.ajax({
                        url: "form.php",
                        type: "post",
                        data: parameter,
                        cache: false,

                    }).done((data) => {
                        console.log("data=" + data);

                        if (data == "success") {
                            location.assign("main.php");
                        }else{
                            alert("投稿に失敗しました。");
                        }
                    }).fail((data)=>{
                        console.log("data=" + data);
                        alert("投稿に失敗しました。");
                    });
                }

                //編集投稿
            } else if ($(".edit_post").css("display") == "block") {

                $.ajax({
                    url: "edit.php",
                    type: "post",
                    data: parameter,
                    cache: false,

                }).done((data) => {
                    console.log("data=" + data);

                    if (data == "success") {
                        location.assign("main.php");
                    }
                });
            }
        }
    });


    //ソート順の変更
    $(".checkbox").on("click", function () {
        let id = $(this).attr('data-id');
        console.log("id=" + id);
        location.assign(`checkbox.php?id=${id}`);
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

        const date = $(`span[data-id=${id}]`).attr("data-date");
        const time = $(`span[data-id=${id}]`).attr("data-time");
        const hour = time.slice(0, 2);
        const minute = time.slice(3, 5);
        const content = $(`p[data-id=${id}]`).text();
        const no_limit = $(this).attr("data-no_limit");


        $(".hidden_id").attr("value", id);//編集するタスクのidを記録しておく。

        $("textarea").val(content);

        switch (no_limit) {
            case 0:
                $("input[type='date']").val(date);
                $("input[name='hour']").val(hour);
                $("input[name='minute']").val(minute);
                break;
            case 1:
                //デフォルト
                let date = new Date();
                let Year = date.getFullYear();
                let month = date.getMonth() + 1;
                let date_ = date.getDate();
                let Ymd = Year + "-" + month + "-" + date_;
                $("input[type='date']").val(Ymd);
                $("input[name='hour']").val(12);
                $("input[name='minute']").val(0);
                break
        }


        const priority = $(this).attr("data-priority");
        console.log("priority:" + priority);
        $(`input[name=priority][value=${priority}]`).prop("checked", true);


        if (no_limit == 1) {
            $("#no_limit").prop("checked", true);
            $("input[type=date]").attr("disabled", true);
            $("input[type=number]").attr("disabled", true);

        }
    });

    //全て削除ボタン
    $("a.delete_done").on("click", (e) => {
        console.log($(".done_num").text());

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
        location.assign("main.php");
    })

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
        console.log("start");
        const val = $(this).prop("value");
        console.log("val=" + val);
        location.assign("order.php?order=" + val);
    })

    //通常モードか残存モードか
    $(".show_mode").on("change", function () {
        console.log("start");
        const val = $(this).prop("value");
        console.log("val=" + val);
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
        hour=append_0(hour);

        let minute = date.getMinutes();
        minute=append_0(minute);

        let Hi = hour + ":" + minute;
        $(".Hi").text(Hi);
    }
    timer();
    setInterval(timer, 1000);

    function append_0(str){
        str ="0"+str;
        str=str.slice(-2);
        return str;
    }

    //footerの出し入れ
    $(".close").on("click", () => {
        $("footer").css("bottom", "-170px");

        $.ajax({
            url: "footer.php",
            type: "get",
            data: "footer=close",
            cache: false,
        });
    })

    $(".open").on("click", () => {
        $("footer").css("bottom", "0");
        $("textarea").focus();

        $.ajax({
            url: "footer.php",
            type: "get",
            data: "footer=open",
            cache: false,
        });
    });
    
});


