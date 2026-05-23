document.addEventListener("DOMContentLoaded", () => {
    fetch("./php/get_leaderboard.php")
        .then(res => res.json())
        .then(data => {
            const board = document.getElementById("leaderboard");

            if (!data.length) {
                board.innerHTML = "<p>There are no results yet.</p>";
                return;
            }

            board.innerHTML = "";

            data.forEach((user, index) => {
                const row = document.createElement("div");

                const medals = ["🥇", "🥈", "🥉"];
                const medal = medals[index] || `#${index + 1}`;

                row.innerHTML = `
                    <span>${medal} ${user.username}</span>
                    <span class="points">${user.points} 🏆</span>
                `;

                board.appendChild(row);
            });
        });



    const tab = document.getElementById("leaderboard-tab");
    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;

    tab.addEventListener("mousedown", e => {
        isDragging = true;
        offsetX = e.clientX - tab.offsetLeft;
        offsetY = e.clientY - tab.offsetTop;
        tab.style.cursor = "grabbing";
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
        tab.style.cursor = "grab";
    });

    document.addEventListener("mousemove", e => {
        if (!isDragging) return;

        tab.style.left = e.clientX - offsetX + "px";
        tab.style.top = e.clientY - offsetY + "px";
    });
});
