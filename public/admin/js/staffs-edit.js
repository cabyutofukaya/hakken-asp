(window.webpackJsonp=window.webpackJsonp||[]).push([[13],{313:function(t,o,n){t.exports=n(314)},314:function(t,o){var n=function(){$("#showPassword").text("password"===$("#password").attr("type")?"表示":"非表示")};$((function(){n(),$("#showPassword").on("click",(function(t){t.preventDefault(),$("#password").attr("type","password"===$("#password").attr("type")?"text":"password"),n()}))})),$((function(){$("#updateButton").on("click",(function(t){t.preventDefault(),$("#updateForm").trigger("submit")}))})),$((function(){$("#deleteButton").on("click",(function(t){t.preventDefault(),$("#deleteForm").trigger("submit")}))}))}},[[313,0]]]);