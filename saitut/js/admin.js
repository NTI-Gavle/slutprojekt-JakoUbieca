function deleteUser(userId) {
    if (confirm("Are you sure you want to delete this user? This action cant be undone.")) {
        fetch('delete_user.php?id=' + userId)
            .then(res => res.text())
            .then(data => {
                if (data === "success") {
                    location.reload();
                } else {
                    alert("Error: " + data);
                }
            });
    }
}

function deleteQuiz(quizId) {
    if (confirm("Are you sure you want to delete this quiz? All its questions will be removed too.")) {
        fetch('delete_quiz.php?id=' + quizId)
            .then(res => res.text())
            .then(data => {
                if (data === "success") {
                    location.reload();
                } else {
                    alert("Error: " + data);
                }
            });
    }
}
