/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./src/Resources/**/*.blade.php", "./src/Resources/**/*.js"],

    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1440px",
            },

            padding: {
                DEFAULT: "90px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            1180: "1180px",
            1060: "1060px",
            991: "991px",
            868: "868px",
        },

        extend: {
            /**
             * Names are kept from the original navy palette to avoid a repo-wide
             * rename; the values underneath are the current charcoal/amber theme.
             */
            colors: {
                navyBlue: "#111113",
                navySurface: "#1A1A1D",
                navySurfaceHover: "#242427",
                navyBorder: "#302F2A",
                offWhite: "#F5F3EE",
                mutedBlue: "#A8A29A",
                photoBackdrop: "#F4F2ED",
                darkGreen: '#40994A',
                darkBlue: '#E8A33D',
                darkPink: '#F85156',
            },

            fontFamily: {
                poppins: ["Poppins", "sans-serif"],
                dmserif: ["DM Serif Display", "serif"],
            },
        }
    },

    plugins: [],

    safelist: [
        {
            pattern: /icon-/,
        }
    ]
};
