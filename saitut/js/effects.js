
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.quiz-card');
    cards.forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const xc = rect.width / 2;
            const yc = rect.height / 2;
            const dx = x - xc;
            const dy = y - yc;
            card.style.transform = `perspective(1000px) rotateX(${-dy / 20}deg) rotateY(${dx / 20}deg) translateY(-10px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)`;
        });
    });
});

window.Effects = {
    
    showCorrect: function() {
        const island = document.getElementById('quiz-container');
        if (island) {
            island.style.transition = "transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.2s";
            island.style.transform = "scale(1.03)";
            island.style.boxShadow = "0 0 70px rgba(46, 204, 113, 0.8), 0 20px 50px rgba(0, 0, 0, 0.3)";
            
            document.body.style.transition = "background-color 0.3s";
            const originalBg = document.body.style.backgroundColor;
            document.body.style.backgroundColor = "rgba(46, 204, 113, 0.15)";
            
            setTimeout(() => {
                island.style.transform = "scale(1)";
                island.style.boxShadow = "0 20px 50px rgba(0, 0, 0, 0.3)";
                document.body.style.backgroundColor = originalBg;
            }, 500);
        }
    },

    
    showWrong: function() {
        const island = document.getElementById('quiz-container');
        if (island) {
            island.classList.add('extreme-shake');
            document.body.style.transition = "background-color 0.1s";
            document.body.style.backgroundColor = "rgba(231, 76, 60, 0.4)";
            
         
            island.style.filter = "sepia(1) saturate(5) hue-rotate(-50deg)";

            setTimeout(() => {
                island.classList.remove('extreme-shake');
                document.body.style.backgroundColor = "";
                island.style.filter = "";
            }, 400);
        }
    },

   
    showVictory: function(scorePercent = 0) {
        if (document.getElementById('victory-screen')) return;

        document.body.style.overflow = "hidden";
        const overlay = document.createElement('div');
        overlay.id = "victory-screen";
        overlay.style.cssText = "position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.95); display:flex; flex-direction:column; justify-content:center; align-items:center; z-index:9999; animation: fadeIn 0.8s forwards; backdrop-filter: blur(10px);";
        
       
        const isSuperb = scorePercent >= 80;
        const title = isSuperb ? "🔥 A legendary performance!" : "🏆 QUIZ FINISHED!";
        const subtext = isSuperb ? `You are a true legend ${scorePercent}%!` : `Your score is ${scorePercent}%`;
        const color = isSuperb ? "#00ffcc" : "#ffcc00";

        overlay.innerHTML = `
            <div class="victory-content" style="text-align: center; animation: slideUp 0.6s ease-out;">
               <h1 class="victory-title" style="color: ${color}; text-shadow: 0 0 30px ${color}; margin: 0; font-family: sans-serif; font-weight: 900;">${title}</h1>
<p class="victory-text" style="color: white; margin: 20px 0; opacity: 0.9;">${subtext}</p>
                <div style="margin-top: 40px;">
                    <button onclick="window.location.href='dashboard.php'" style="padding: 20px 50px; font-size: 1.6rem; cursor: pointer; border-radius: 50px; border: none; background: white; color: black; font-weight: bold; transition: 0.3s; box-shadow: 0 10px 20px rgba(255,255,255,0.2);">Back to Main Menu</button>
                </div>
            </div>
            <div class="confetti-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></div>
        `;

        document.body.appendChild(overlay);
        
        
        const count = isSuperb ? 150 : 50;
        this.createConfetti(count);
    },

    createConfetti: function(count) {
        const container = document.querySelector('.confetti-container');
        if (!container) return;
        for (let i = 0; i < count; i++) {
            const confetti = document.createElement('div');
            const size = Math.random() * 12 + 5 + "px";
            confetti.style.cssText = `
                position: absolute;
                width: ${size};
                height: ${size};
                background-color: hsl(${Math.random() * 360}, 100%, 50%);
                left: ${Math.random() * 100}vw;
                top: -20px;
                opacity: ${Math.random()};
                border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
                transform: rotate(${Math.random() * 360}deg);
                animation: fall ${2 + Math.random() * 4}s linear forwards;
            `;
            container.appendChild(confetti);
        }
    }
};


if (!document.getElementById('quiz-effects-styles')) {
    const style = document.createElement('style');
    style.id = 'quiz-effects-styles';
    style.innerHTML = `
        @keyframes extreme-shake {
            0% { transform: translate(0,0); }
            10% { transform: translate(-10px, -10px) rotate(-2deg); }
            20% { transform: translate(10px, 10px) rotate(2deg); }
            30% { transform: translate(-10px, 10px) rotate(-1deg); }
            40% { transform: translate(10px, -10px) rotate(1deg); }
            50% { transform: translate(-10px, -10px) rotate(-2deg); }
            100% { transform: translate(0,0); }
        }
        .extreme-shake { animation: extreme-shake 0.3s linear; }
        @keyframes fall {
            to { transform: translateY(110vh) rotate(720deg); opacity: 0; }
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    `;
    document.head.appendChild(style);
}