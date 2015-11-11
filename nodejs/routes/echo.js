// JavaScript source code
module.exports = {
    'get': {
        routeString:'echo/:name',
        fn: function (req, res, next) {
            res.send(req.params);
            return next();
        }
    }
};