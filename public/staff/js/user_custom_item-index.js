(window.webpackJsonp=window.webpackJsonp||[]).push([[86],{369:function(n,i,t){n.exports=t(370)},370:function(n,i){$((function(){$("[name='flg[]']").on("click",(function(){$(this).prop("disabled",!0);var n=$(this).data("agency_account");axios.post("/api/".concat(n,"/toggleFlg"),{id:$(this).val(),flg:$(this).is(":checked"),_method:"put"}).then((function(n){})).catch((function(n){alert("有効・無効フラグの更新に失敗しました。")})).finally(function(){$(this).prop("disabled",!1)}.bind(this))}))}))}},[[369,0]]]);