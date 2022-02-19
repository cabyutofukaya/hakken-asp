$(function(){
  $('#editForm').submit(function(){
    const rd = $("[name=return_date]").val();
    if(rd){
      const dt = new Date();
      const returnDate = new Date(`${rd} 23:59:59`);
      if(dt.getTime() > returnDate.getTime()){ // 帰着日が過去
        if(confirm("帰着日が過去の日付で登録すると催行済に移動します。\nよろしいですか?")){
          return true;
        }else{
          $("form .doubleBan").css("pointer-events", "auto"); // 二重送信防止class除去
          return false;
        }
      }
    }
    return true;
  })
});