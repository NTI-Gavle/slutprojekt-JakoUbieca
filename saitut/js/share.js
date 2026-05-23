function shareContent(title, text, customUrl = null) {
    const urlToShare = customUrl || window.location.href;

    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: urlToShare
        }).catch(err => {
            console.log('Error sharing:', err);
        });
    } else {
        navigator.clipboard.writeText(urlToShare).then(() => {
            alert("Link copied to clipboard!");
        }).catch(() => {
            alert("Sharing not supported on this browser.");
        });
    }
}
