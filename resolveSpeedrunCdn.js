const puppeteer = require("puppeteer");

(async () => {
    const url = process.argv[2];
    if (!url) {
        console.error("No URL provided");
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        headless: true,
        args: ["--no-sandbox", "--disable-setuid-sandbox"]
    });

    const page = await browser.newPage();

    // Ловим все запросы
    let cdnUrl = null;
    page.on("response", async (response) => {
        try {
            const headers = response.headers();
            const contentType = headers["content-type"];

            if (contentType && contentType.startsWith("image")) {
                const finalUrl = response.url();

                // игнорируем Cloudflare URL
                if (!finalUrl.includes("speedrun.com/static")) {
                    cdnUrl = finalUrl;
                }
            }
        } catch (e) {
            // игнорируем
        }
    });

    await page.goto(url, {
        waitUntil: "networkidle0",
        timeout: 30000
    });

    // заменяем waitForTimeout
    await new Promise(r => setTimeout(r, 2500));

    await browser.close();

    if (cdnUrl) {
        console.log(cdnUrl);
    } else {
        console.error("CDN image not found");
        process.exit(1);
    }
})();
