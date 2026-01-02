import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    encrypted: true,
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                // Use the same token key as auth store (shopAccessToken)
                const token = localStorage.getItem("shopAccessToken");

                if (!token) {
                    console.error("No authentication token found");
                    callback(new Error("Not authenticated"));
                    return;
                }

                // Get base URL from window.axios or use default
                const baseURL = (window.axios && window.axios.defaults.baseURL) || '/';
                const authURL = `${baseURL}/api/v1/auth/broadcasting/auth`;
                
                fetch(authURL, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channel.name,
                    }),
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error(
                                `HTTP error! status: ${response.status}`
                            );
                        }
                        return response.json();
                    })
                    .then((data) => {
                        callback(null, data);
                    })
                    .catch((error) => {
                        console.error("Broadcasting auth error:", error);
                        callback(error);
                    });
            },
        };
    },
});

// Connection status logging (optional, for debugging)
echo.connector.pusher.connection.bind("connected", function () {
    console.log("✅ Pusher connected successfully!");
});

echo.connector.pusher.connection.bind("error", function (err) {
    console.error("❌ Pusher connection error:", err);
});

echo.connector.pusher.connection.bind("disconnected", function () {
    console.log("⚠️ Pusher disconnected");
});

// Expose Echo globally for access in Vue components
window.Echo = echo;

export default echo;
