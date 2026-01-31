export async function useApi(url, data = {}, delay = 2000) {
    const params = new URLSearchParams();

    for (const [key, value] of Object.entries(data)) {
        if (value !== null && value !== undefined && value !== "") {
            params.append(key, value);
        }
    }

    const queryString = params.toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;

    return new Promise((resolve, reject) => {
        setTimeout(async () => {
            try {
                const response = await fetch(fullUrl);
                resolve(await response.json());
            } catch (e) {
                reject(e);
            }
        }, delay);
        console.log(delay);
    });
}
