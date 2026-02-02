import ky from "ky";

export async function useApi({ url = "", data = {}, debug = false } = {}) {
    if (!url) throw new Error("Request error");

    try {
        if (debug) console.log("[API] request", { url, data });

        const resultData = await ky.get(url, { searchParams: data }).json();

        // const resultData = await ky.get(url, { json: data }).json();

        if (debug) console.log("[API] response", resultData);
        return resultData;
    } catch (e) {
        if (debug) console.log("[API] error", e);

        // ky HTTP errors
        if (e?.name === "HTTPError") {
            const status = e.response?.status;

            if (status === 500) {
                const serverBody = await e.response.text().catch(() => "");
                console.log("[API] 500 body:", serverBody);

                e.message = "Internal server error";
            } else {
                e.message = `HTTP ${status}`;
            }
        }
        throw e;
    } finally {
    }
}
