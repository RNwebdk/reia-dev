async function getArticles() {
    let response = await fetch("/articles.json", {
        headers: {
            "Content-Type": "application/json"
        }
    });
    return response.json();
}
async function checkLinks() {
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
checkLinks();

let images = document.querySelectorAll(".double");

images.forEach((image) => {
    image.width = image.width * 2;
    image.height = image.height * 2;
});
