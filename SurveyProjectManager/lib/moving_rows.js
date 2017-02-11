function get_previoussibling(n){
    x=n.previousSibling;
    while (x.nodeType!=1){
        x=x.previousSibling;
    }
    return x;
}

function get_nextsibling(n){
    x=n.nextSibling;
    while ( x != null && x.nodeType!=1){
        x=x.nextSibling;
    }
    return x;
}

function rowUp(tbl_id, row_id){
    var table=document.getElementById(tbl_id);
    var row_num;
    for (var i=1;i<table.rows.length;i++){
        if (table.rows[i].id==row_id){
            row_num = i;
        }
    }
    if (row_num > 1){
        var row = table.rows[row_num];
        var tableNode = row.parentNode;
        tableNode.insertBefore ( row, get_previoussibling( row ) );
    }
}

function rowDown(tbl_id, row_id){
    var table=document.getElementById(tbl_id);
    var row_num;
    for (var i=1;i<table.rows.length;i++){
        if (table.rows[i].id==row_id){
            row_num = i;
        }
    }
    if (row_num > 0){
        var row = table.rows[row_num];
        var tableNode = row.parentNode;
        tableNode.insertBefore (row, get_nextsibling(get_nextsibling(row)));
    }
}