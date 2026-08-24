$(function(){
    // Rich text editor
    const editor = document.getElementById('rich-editor');
    const content = document.getElementById('content');
    if(editor && content){
        document.querySelectorAll('[data-cmd]').forEach(btn=>{
            btn.addEventListener('click',()=>document.execCommand(btn.dataset.cmd,false,null));
        });
        const sync=()=>content.value=editor.innerHTML.trim();
        editor.addEventListener('input',sync);
        editor.closest('form')?.addEventListener('submit',sync);
    }

    // Exam countdown + REST API submit
    const form=document.getElementById('exam-form');
    const timer=document.getElementById('countdown');
    if(form && timer){
        const start=new Date(timer.dataset.start.replace(' ','T')+'+07:00').getTime();
        const duration=parseInt(timer.dataset.minutes,10)*60*1000;
        let submitting=false;

        function render(){
            const remaining=Math.max(0,start+duration-Date.now());
            const sec=Math.floor(remaining/1000);
            const mm=String(Math.floor(sec/60)).padStart(2,'0');
            const ss=String(sec%60).padStart(2,'0');
            timer.textContent=mm+':'+ss;
            if(remaining<=0 && !submitting){
                submitting=true;
                submitExam(true);
            }
        }
        async function submitExam(auto){
            const fd=new FormData(form);
            const answers={};
            for(const [key,value] of fd.entries()){
                const m=key.match(/^answers\[(\d+)\]$/);
                if(m) answers[m[1]]=value;
            }
            try{
                const res=await fetch(form.dataset.submitUrl,{
                    method:'POST',
                    headers:{'Content-Type':'application/json','Accept':'application/json'},
                    body:JSON.stringify({answers:answers})
                });
                const data=await res.json();
                if(data.ok) window.location.href=data.redirect;
                else { alert(data.message || 'Không thể nộp bài.'); submitting=false; }
            }catch(err){
                alert('Lỗi kết nối khi nộp bài. Hãy thử lại.');
                submitting=false;
            }
        }
        form.addEventListener('submit',function(e){
            e.preventDefault();
            if(submitting)return;
            if(confirm('Bạn chắc chắn muốn nộp bài?')){
                submitting=true;
                submitExam(false);
            }
        });
        render();
        setInterval(render,1000);
    }
});
