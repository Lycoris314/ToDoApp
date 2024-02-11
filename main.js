$(() => {
    console.log("更新確認8");

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
                        // switch (data) {
                        //     case "over task":
                        //         alert("タスクの登録上限{100}件に達しています");
                        //         break;
                        //     case "success":
                        //         location.assign("main.php");
                        //         break;
                        // }
                        if (data == "success") {
                            location.assign("main.php");
                        }
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

        $(".new_post").css("display", "none");
        $(".edit_post").css("display", "block");
        $(".back").css("display", "block");
        $(".edit_post_buttons").css("display","flex");

        $("footer").css("bottom","0");

        // $("html").animate(
        //     { scrollTop: $("html").outerHeight() }, 800
        // );

        const id = $(this).attr("data-id");

        // const datetime = $(`span[data-id=${id}]`).attr("data-date") + " "
        //     + $(`span[data-id=${id}]`).attr("data-time");

        // console.log(datetime);

        // date = datetime.slice(0, 10);
        // hour = datetime.slice(11, 13);
        // minute = datetime.slice(14, 16);

        const date = $(`span[data-id=${id}]`).attr("data-date");
        const time = $(`span[data-id=${id}]`).attr("data-time");
        const hour = time.slice(0, 2);
        const minute = time.slice(3, 5);


        $(".hidden_id").attr("value", id);//編集するタスクのidを記録しておく。

        const content = $(`p[data-id=${id}]`).text();
        $("textarea").val(content);
        $("input[type='date']").val(date);
        $("input[name='hour']").val(hour);
        $("input[name='minute']").val(minute);

        console.log("ここまで");
        const priority =$(this).attr("data-priority");
        console.log("priority:"+priority);
        $(`input[name=priority][value=${priority}]`).prop("checked",true);

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
        console.log($(this).prop("checked"));
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

    function timer(){
                let date=new Date();
        let Year=date.getFullYear();
        let month=date.getMonth()+1;
        let date_=date.getDate();
        let Ymd=Year+"-"+month+"-"+date_;
        $(".Ymd").text(Ymd);
        let hour=date.getHours();
        let minute=date.getMinutes();
        let Hi=hour+":"+minute;
        $(".Hi").text(Hi);
    }

    timer();
    setInterval(timer,1000);

    $(".close").on("click",()=>{
        $("footer").css("bottom","-170px");
        //$(".open").css("position","fixed");
    })

    $(".open").on("click",()=>{
        $("footer").css("bottom","0");
    })

});


