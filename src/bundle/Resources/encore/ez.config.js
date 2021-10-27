const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('ezrecommendation-client-js', [ path.resolve(__dirname, '../public/js/EzRecommendationClient.js') ]);
    Encore.addEntry('ezrecommendation-client-css', [ path.resolve(__dirname, '../public/css/recommendations.css') ]);
};
