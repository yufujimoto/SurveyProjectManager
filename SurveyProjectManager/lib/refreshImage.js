function refreshAvatar(id,h,w,target){
    var param_w = "&width="+w;
    var param_h = "&height="+h;
    var param_target = "&table="+target;
    
    if (param_w == "&width="){ param_w = ""; }
    if (param_h == "&height="){ param_h = ""; }
    if (param_target == "&table="){ param_target = ""; }
    
    var form = document.getElementById('form_avatar');
    var filename = document.getElementById('name_avatar');
    var input = document.getElementById('input_avatar');
        
    form.action = "avatar_uploaded.php?id="+id+param_h+param_w+param_target;    
    form.target = "iframe_avatar";
}


