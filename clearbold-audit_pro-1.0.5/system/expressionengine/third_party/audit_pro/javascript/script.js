$(document).ready(function(){
    $('table.mainTable thead th').removeAttr("onclick").unbind("click");
    $('table.mainTable > thead th.no-sort').unbind();
})