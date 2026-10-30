import https from "node:https";
import http from "node:http";
import fs from "node:fs";

const certPath = new URL("../storage/certs/lan.crt", import.meta.url);
const keyPath = new URL("../storage/certs/lan.key", import.meta.url);

const server = https.createServer(
    {
        cert: fs.readFileSync(certPath),
        key: fs.readFileSync(keyPath),
    },
    (req, res) => {
        const forwardedFor = req.headers["x-forwarded-for"]
            ? `${req.headers["x-forwarded-for"]}, ${req.socket.remoteAddress}`
            : req.socket.remoteAddress;

        const proxyReq = http.request(
            {
                host: "127.0.0.1",
                port: 8000,
                path: req.url,
                method: req.method,
                headers: {
                    ...req.headers,
                    host: req.headers.host ?? "127.0.0.1:8000",
                    "x-forwarded-for": forwardedFor,
                    "x-forwarded-host": req.headers.host ?? "127.0.0.1:8000",
                    "x-forwarded-port": "8443",
                    "x-forwarded-proto": "https",
                },
            },
            (proxyRes) => {
                res.writeHead(proxyRes.statusCode, proxyRes.headers);
                proxyRes.pipe(res);
            }
        );

        proxyReq.on("error", () => {
            res.writeHead(502, { "Content-Type": "text/plain" });
            res.end("Bad gateway");
        });

        req.pipe(proxyReq);
    }
);

server.listen(8443, "0.0.0.0", () => {
    console.log("HTTPS proxy running on https://0.0.0.0:8443 -> http://127.0.0.1:8000");
});
