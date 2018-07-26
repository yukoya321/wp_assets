(function(){
  'use strict'
  function toggleClass(e){
      var next = this.nextElementSibling;
      if(next.classList.contains('open')){
        next.style.height = "";
        this.classList.remove('accordion__open');
        this.classList.add('accordion');
        next.classList.remove('open');
      }else{
        var Y = next.scrollHeight;
        next.style.height = Y+"px";
        this.classList.add('accordion__open');
        this.classList.remove('accordion');
        next.classList.add('open');
      }
  }
  if(document.getElementById('archives__accordion')){
   var catEl = document.getElementById('archives__accordion');
       catEl.addEventListener('click', toggleClass);
  }
  if(document.getElementById('archives__accordion')){
   var arcEl = document.getElementById('category__accordion');
       arcEl.addEventListener('click', toggleClass);
  }
  
}());