function refreshAvatar(id,h,w,target){
    var form = document.getElementById('form_avatar');
    var filename = document.getElementById('name_avatar');
    var input = document.getElementById('input_avatar');
    
    form.action = "avatar_uploaded.php?id="+id+"&height="+h+"&width="+w+"&table="+target;    
    form.target = "iframe_avatar";
}


