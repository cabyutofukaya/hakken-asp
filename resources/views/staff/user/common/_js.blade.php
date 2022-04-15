<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> 
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script> 
<script>
    flatpickr.localize(flatpickr.l10ns.ja);
    flatpickr('.calendar input', {
        allowInput: true,
		dateFormat: "Y/m/d"
    });
</script>
<script>
  $(function(){
    $("#submit").on("click", function(){
      if(!$("[name='userable\\[user_ext\\]\\[age_kbn\\]']").val()){ // 「年齢区分」が未設定の場合は注意アラート
        if(!confirm("「年齢区分」が設定されていません。よろしいですか?")){
          $(this).css("pointer-events", "auto"); // 二重送信防止styleを除去
          return false;
        }
      }
      return true;
    });
  });
</script>