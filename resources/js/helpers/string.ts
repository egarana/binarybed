export function capitalizeFirst(str) {
    if (!str || typeof str !== "string") return "";
    str = str.trim();
    return str.charAt(0).toUpperCase() + str.slice(1);
}