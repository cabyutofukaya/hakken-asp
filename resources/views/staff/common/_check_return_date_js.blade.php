$(function(){
  $('#editForm').submit(function(){
    const rd = $("[name=return_date]").val();
    if(rd){
      const dt = new Date();
      const returnDate = new Date(`${rd} 23:59:59`);
      if(dt.getTime() > returnDate.getTime()){ // 帰着日が過去
        return confirm("帰着日が過去の日付で登録すると催行済に移動します。\nよろしいですか?");
      }
    }
    return true;
  })
});