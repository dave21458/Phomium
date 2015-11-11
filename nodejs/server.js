// npm requirements
var _, async, restify, util,
    //local requirements
    routes,
    // locals
    initRoutes, server;

_ = require('underscore');
async = require('async');
restify = require('restify');
util = require('util');

routes = require('./routes');

server = restify.createServer({
    name: 'restify-phomium',
    version: '1.0.0'
});

server.use(restify.acceptParser(server.acceptable));
server.use(restify.queryParser());
server.use(restify.bodyParser());



server.listen(8080, function () {
    console.log('%s listening at %s', server.name, server.url);
});

initRoutes = function (svr) {
    var eachRoutes, eachRouteName, eachVerb;

    _.each(routes, function(routeBlock, routeBlockName) {
        // extract route block name for example 'echo'
        _.each(routeBlock, function (route, routeVerb) {
            // now write the server entry
            server[routeVerb](route.routeString, route.fn);
        });
    });

}(server);
