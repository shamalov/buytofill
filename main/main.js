document.addEventListener('DOMContentLoaded',e=>{let b=e.target.querySelector('.preloader');if(b){b.style.opacity=0;b.addEventListener('transitionend',()=>b.style.display='none');setTimeout(()=>b.style.display='none',400)}});

let master=document.querySelector('#master-checkbox');
if(master) master.addEventListener('click',()=>{master.parentNode.parentNode.parentNode.parentNode.parentNode.querySelectorAll('input[name="response-checkbox"]').forEach(a=>{a.checked=master.checked})});