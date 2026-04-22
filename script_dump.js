
    function safeChart(id,opts){
        var el=document.querySelector('#'+id);
        if(!el)return;
        el.innerHTML='';
        try{new ApexCharts(el,opts).render();}catch(e){console.warn('Chart error:',e);}
    }
    
    function initCharts(){
        console.log('Initializing charts...');
        var hist = [];
        var fore = [];
        var forecastEl = document.querySelector('#chart-forecast');
        if(forecastEl) {
            if(hist.length>0){
                safeChart('chart-forecast',{chart:{type:'area',height:300,toolbar:{show:false}},series:[{name:'Revenue',data:hist.map(h=>h.total)},{name:'Forecast',data:hist.map(()=>null).concat(fore.map(f=>f.total))}],xaxis:{categories:hist.map(h=>h.date).concat(fore.map(f=>f.date)),labels:{show:false}},colors:['#6366f1','#a78bfa'],stroke:{width:[3,2],dashArray:[0,5]},fill:{type:['gradient','gradient'],gradient:{shadeIntensity:1,opacityFrom:.4,opacityTo:.05}},tooltip:{theme:'dark'},grid:{strokeDashArray:3,borderColor:'#f1f5f9'}});
            } else {
                forecastEl.innerHTML = '<div style="display:flex;height:100%;align-items:center;justify-content:center;color:#94a3b8;font-size:12px">No revenue data available for this period</div>';
            }
        }
        
        var hourly = [{"h":"00","v":2},{"h":"04","v":0},{"h":"08","v":0},{"h":"12","v":0},{"h":"16","v":0},{"h":"20","v":0}];
        if(hourly.length>0){safeChart('chart-hourly',{chart:{type:'bar',height:180,toolbar:{show:false}},series:[{name:'Visits',data:hourly.map(h=>h.v)}],xaxis:{categories:hourly.map(h=>h.h)},colors:['#6366f1'],plotOptions:{bar:{borderRadius:4}},tooltip:{theme:'dark'},grid:{strokeDashArray:3,borderColor:'#f1f5f9'}});}
        
        var traf = [{"source":"Referral","count":119916},{"source":"Google","count":40063},{"source":"Facebook","count":40042},{"source":"Direct","count":36},{"source":"ig","count":11},{"source":"fb","count":8}];
        if(traf.length>0){safeChart('chart-traffic',{chart:{type:'donut',height:240},series:traf.map(t=>t.count),labels:traf.map(t=>t.source),colors:['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981'],legend:{position:'bottom',fontSize:'11px'},tooltip:{theme:'dark'}});}
        
        var fc = [];
        if(fc.map && fc.length>0){safeChart('chart-finance',{chart:{type:'bar',height:260,toolbar:{show:false}},series:[{name:'Commission',data:fc.map(f=>f.commission)},{name:'Refunds',data:fc.map(f=>f.refunds)}],xaxis:{categories:fc.map(f=>f.month)},colors:['#6366f1','#f87171'],plotOptions:{bar:{borderRadius:4}},tooltip:{theme:'dark'},grid:{strokeDashArray:3,borderColor:'#f1f5f9'}});}
        
        var rt = [{"date":"Oct","value":0},{"date":"Nov","value":0},{"date":"Dec","value":0},{"date":"Jan","value":0},{"date":"Feb","value":0},{"date":"Mar","value":0},{"date":"Apr","value":0}];
        if(rt.map && rt.length>0){safeChart('chart-refund',{chart:{type:'area',height:120,toolbar:{show:false},sparkline:{enabled:true}},series:[{name:'Refund %',data:rt.map(r=>r.value)}],colors:['#f87171'],fill:{type:'gradient',gradient:{opacityFrom:.4,opacityTo:.05}},tooltip:{theme:'dark'}});}
        
        var vg = [{"month":"Oct","active":6,"new":0,"churned":0},{"month":"Nov","active":6,"new":0,"churned":0},{"month":"Dec","active":8,"new":2,"churned":0},{"month":"Jan","active":8,"new":0,"churned":0},{"month":"Feb","active":11,"new":3,"churned":0},{"month":"Mar","active":13,"new":2,"churned":0},{"month":"Apr","active":13,"new":0,"churned":0}];
        if(vg.map && vg.length>0){safeChart('chart-vendor-growth',{chart:{type:'bar',height:240,toolbar:{show:false}},series:[{name:'Active',data:vg.map(v=>v.active)},{name:'New',data:vg.map(v=>v.new)}],xaxis:{categories:vg.map(v=>v.month)},colors:['#6366f1','#10b981'],plotOptions:{bar:{borderRadius:4}},tooltip:{theme:'dark'},grid:{strokeDashArray:3,borderColor:'#f1f5f9'}});}
        
        var cd = [];
        if(cd.map && cd.length>0){safeChart('chart-categories',{chart:{type:'donut',height:240},series:cd.map(c=>c.value),labels:cd.map(c=>c.name),colors:['#6366f1','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4'],legend:{position:'bottom',fontSize:'11px'},tooltip:{theme:'dark'}});}
    }
    
    document.addEventListener('livewire:init', () => {
        initCharts();
        Livewire.hook('morph.updated', ({ el, component }) => {
            if (component.name === 'analytics.technical-dashboard') {
                setTimeout(initCharts, 50);
            }
        });
    });

    // Fallback
    window.onload = function() {
        setTimeout(initCharts, 500);
    };
    