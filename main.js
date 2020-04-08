async function getArticles() {
    let response = await fetch("/articles.json", {
        headers: {
            "Content-Type": "application/json"
        }
    });
    return response.json();
}
async function checkLinksWiki() {
    let response = await getArticles().then((response) => {
        let articleBody = document.querySelector(".article-body");

        if (articleBody) {
            let links = document.querySelector(".article-body").getElementsByTagName("a");
            let responseArray = Object.values(response);
        
            Array.from(links).forEach((link) => {
                let validUrl = /\/wiki\/article\/([a-z0-9_-]+)/;
                let href = link.href;
                let match = href.match(validUrl);
        
                if (match) {
                    let articleExists = false;
    
                    if (responseArray.indexOf(match[1]) !== -1) {
                        articleExists = true;
                    }
                    if (!articleExists) {
                        link.classList.add("invalid-link");
                    }
                }
            });
        }
    });
}
async function checkLinksForum() {
    let response = await getArticles().then((response) => {
        let topicPosts = document.querySelectorAll(".topic-post");

        if (topicPosts) {
            let posts = document.querySelectorAll(".topic-post");
            Array.from(posts).forEach((post) => {
                let links = post.getElementsByTagName("a");
                let responseArray = Object.values(response);
            
                Array.from(links).forEach((link) => {
                    let validUrl = /\/wiki\/article\/([a-z0-9_-]+)/;
                    let href = link.href;
                    let match = href.match(validUrl);
            
                    if (match) {
                        let articleExists = false;
        
                        if (responseArray.indexOf(match[1]) !== -1) {
                            articleExists = true;
                        }
                        if (!articleExists) {
                            link.classList.add("invalid-link");
                        }
                    }
                });
            });
        }
    });
}
checkLinksWiki();
checkLinksForum();
/*
function generateQuoteButtons() {
    let topicPosts = document.querySelectorAll(".topic-post");
    let replyContent = document.querySelector(".reply-content");

    topicPosts.forEach((post) => {
        let quoteButton = post.querySelector(".post-quote");
        let username = post.querySelector(".post-username").textContent;
        let content = post.querySelector(".post-content").innerText;

        console.log(post);
        console.log(quoteButton);
        console.log(username);
        console.log(content);

        quoteButton.addEventListener("click", () => {
            let str = `<blockquote>*${username} wrote:*\n${content}\n</blockquote>`
            replyContent.value = str;
        }, false);
    });
}
generateQuoteButtons();
*/
let images = document.querySelectorAll(".double");

images.forEach((image) => {
    image.width = image.width * 2;
    image.height = image.height * 2;
});
