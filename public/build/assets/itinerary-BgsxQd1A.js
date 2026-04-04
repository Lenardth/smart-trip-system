const a=new URLSearchParams(window.location.search);a.has("auto_print")&&(window.onload=()=>setTimeout(()=>window.print(),500));
