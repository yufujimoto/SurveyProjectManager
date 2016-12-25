function addSectionRow(tbl_id, crt_mem){
    var table=document.getElementById(tbl_id);
    var cnt_row=table.rows.length;
    var row=table.insertRow(cnt_row);
    
    // Define cells.
    var sct_cll_nam=row.insertCell(0);
    var sct_cll_cdt=row.insertCell(1);
    var sct_cll_mdt=row.insertCell(2);
    var sct_cll_mem=row.insertCell(3);
    var sct_cll_ctr=row.insertCell(4);
    var sct_cll_edt=row.insertCell(5);
    
    var sct_name = prompt("章の名前を入力してください。");
    
    // Define section name.
    var sct_itm_nam=document.createElement("input");
    sct_itm_nam.type="text";
    sct_itm_nam.id="sct_nam_" + tableID;
    sct_itm_nam.name="sct_nam_" + tableID;
    sct_itm_nam.value=sct_name;
    sct_itm_nam.className="form-control";
    sct_cll_nam.style.verticalAlign="middle";
    sct_cll_nam.appendChild(sct_itm_nam);
    
    // Insert current date & time.
    var cur_dt = new Date();
    var cur_dt_txt = cur_dt.getFullYear() + "-" + (cur_dt.getMonth()+1)  + "-" + cur_dt.getDate();
    var cur_tm_txt = cur_dt.getHours() + ":" + cur_dt.getMinutes() + ":" + cur_dt.getSeconds();
    sct_cll_cdt.style.textAlign="center";
    sct_cll_cdt.style.verticalAlign="middle";
    sct_cll_cdt.innerHTML = cur_dt_txt + " " + cur_tm_txt;
    
    // Insert current date & time.
    sct_cll_mdt.style.textAlign="center";
    sct_cll_mdt.style.verticalAlign="middle";
    sct_cll_mdt.innerHTML = cur_dt_txt + " " + cur_tm_txt;
    
    // Insert current date & time.
    sct_cll_mem.style.textAlign="center";
    sct_cll_mem.style.verticalAlign="middle";
    sct_cll_mem.innerHTML = crt_mem;
    
    var frm_grp_1 = document.createElement("div");
    frm_grp_1.className="btn-group";
    
    var sct_itn_up=document.createElement("button");
    sct_itn_up.type=type="submit";
    sct_itn_up.id="sct_up_" + tableID;
    sct_itn_up.name="sct_up_" + tableID;
    sct_itn_up.style.textAlign="right";
    sct_itn_up.className="btn btn-sm btn-primary";
    frm_grp_1.appendChild(sct_itn_up);
    
    var spn_up = document.createElement("span");
    spn_up.className="glyphicon glyphicon-chevron-up";
    sct_itn_up.appendChild(spn_up);
    
    var sct_itn_dwn=document.createElement("button");
    sct_itn_dwn.type=type="submit";
    sct_itn_dwn.name="sct_dwn_" + tableID;
    sct_itn_dwn.className="btn btn-sm btn-primary";
    frm_grp_1.appendChild(sct_itn_dwn);
    
    var spn_dwn = document.createElement("span");
    spn_dwn.className="glyphicon glyphicon-chevron-down";
    sct_itn_dwn.appendChild(spn_dwn);
    sct_cll_ctr.style.width="100px";
    sct_cll_ctr.style.verticalAlign="middle";
    sct_cll_ctr.appendChild(frm_grp_1);
    
    
    var frm_grp_2 = document.createElement("div");
    frm_grp_2.className="btn-group-vertical";
    
    var sct_itn_edt=document.createElement("button");
    sct_itn_edt.type=type="submit";
    sct_itn_edt.id="sct_edt_" + tableID;
    sct_itn_edt.name="sct_edt_" + tableID;
    sct_itn_edt.className="btn btn-sm btn-primary";
    frm_grp_2.appendChild(sct_itn_edt);
    
    var spn_edt = document.createElement("span");
    spn_edt.innerHTML="章の編集";
    sct_itn_edt.appendChild(spn_edt);
    
    var sct_itn_dll=document.createElement("button");
    sct_itn_dll.type=type="submit";
    sct_itn_dll.id="sct_dll_" + tableID;
    sct_itn_dll.name="sct_dll_" + tableID;
    sct_itn_dll.className="btn btn-sm btn-danger";
    frm_grp_2.appendChild(sct_itn_dll);
    
    var spn_dll = document.createElement("span");
    spn_dll.innerHTML="章の削除";
    sct_itn_dll.appendChild(spn_dll);
    sct_cll_edt.style.width="100px";
    sct_cll_ctr.style.verticalAlign="middle";
    sct_cll_edt.appendChild(frm_grp_2);
    
    insertSection();
}