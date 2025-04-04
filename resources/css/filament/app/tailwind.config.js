import preset from "../../../../vendor/filament/filament/tailwind.config.preset";

export default {
    presets: [preset],
    content: [
        "./app/Filament/**/*.php",
        "./resources/views/filament/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
        "./resources/views/components/**/*.blade.php",
        "./vendor/awcodes/filament-table-repeater/resources/css/plugin.css",
        "./vendor/awcodes/filament-table-repeater/resources/views/**/*.blade.php",
    ],
    theme: {
        extend: {
            colors: {
                "primary-bg": "#0c6600",
                "primary-bg-alt": "#cee0cc",
                "secondary-bg": "#cee0cc",
                "secondary-bg-alt": "#cee0cc",
                "primary-text": "#cee0cc",
                "primary-text-alt": "#cee0cc",
                "secondary-text": "#cee0cc",
                "orange-ripe": "#cee0cc",
                "orange-ripe-light": "#cee0cc",
                "main-bg": "#cee0cc",

                primary: {
                    100: "#cee0cc",
                    200: "#9ec299",
                    300: "#6da366",
                    400: "#3d8533",
                    500: "#0c6600",
                    600: "#0a5200",
                    700: "#073d00",
                    800: "#052900",
                    900: "#021400",
                },
                "primary-alt": {
                    100: "#dfe2d7",
                    200: "#bfc4af",
                    300: "#a0a788",
                    400: "#808960",
                    500: "#606c38",
                    600: "#4d562d",
                    700: "#3a4122",
                    800: "#262b16",
                    900: "#13160b",
                },
                secondary: {
                    100: "#cee0cc",
                    200: "#9ec299",
                    300: "#6da366",
                    400: "#3d8533",
                    500: "#0c6600",
                    600: "#0a5200",
                    700: "#073d00",
                    800: "#052900",
                    900: "#021400",
                },
                "secondary-alt": {
                    100: "#ffe7d9",
                    200: "#ffcfb3",
                    300: "#ffb88c",
                    400: "#ffa066",
                    500: "#ff8840",
                    600: "#cc6d33",
                    700: "#995226",
                    800: "#66361a",
                    900: "#331b0d",
                },
            },
        },
    },
};
