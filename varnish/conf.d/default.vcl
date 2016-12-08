// Our Backend - Assuming that web server is listening on port 80
// Replace the host to fit your setup
backend ezpublish {
    .host = "ezpublish";
    .port = "80";
}

// ACL for invalidators IP
acl invalidators {
    "127.0.0.1";
    "192.168.0.0"/16;
}

// ACL for debuggers IP
acl debuggers {
    "127.0.0.1";
    "192.168.0.0"/16;
}

// Called at the beginning of a request, after the complete request has been received
sub vcl_recv {

    // Set the backend
    set req.backend_hint = ezpublish;

    // Advertise Symfony for ESI support
    set req.http.Surrogate-Capability = "abc=ESI/1.0";

    // Add a unique header containing the client address (only for master request)
    // Please note that /_fragment URI can change in Symfony configuration
    if (!req.url ~ "^/_fragment") {
        if (req.http.x-forwarded-for) {
            set req.http.X-Forwarded-For = req.http.X-Forwarded-For + ", " + client.ip;
        } else {
            set req.http.X-Forwarded-For = client.ip;
        }
    }

    // Trigger cache purge if needed
    call ez_purge;

    // Don't cache requests other than GET and HEAD.
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    // Normalize the Accept-Encoding headers
    if (req.http.Accept-Encoding) {
        if (req.http.Accept-Encoding ~ "gzip") {
            set req.http.Accept-Encoding = "gzip";
        } elsif (req.http.Accept-Encoding ~ "deflate") {
            set req.http.Accept-Encoding = "deflate";
        } else {
            unset req.http.Accept-Encoding;
        }
    }

    if (req.http.Cache-Control ~ "no-cache") {
        set req.hash_always_miss = true;
    }

    // Do a standard lookup on assets
    // Note that file extension list below is not extensive, so consider completing it to fit your needs.
    if (req.url ~ "\.(css|js|gif|jpe?g|bmp|png|tiff?|ico|img|tga|wmf|svg|swf|ico|mp3|mp4|m4a|ogg|mov|avi|wmv|zip|gz|pdf|ttf|eot|wof)$") {
        return (hash);
    }

    // If it passes all these tests, do a lookup anyway.
    return (hash);
}

// Called when the requested object has been retrieved from the backend
sub vcl_backend_response {

    if (beresp.status >= 400) {
        set beresp.http.CacheControl = "no-cache";
        set beresp.ttl = 0s;
    }

    set beresp.http.X-Varnish-TTL = beresp.ttl;

    // Optimize to only parse the Response contents from Symfony
    if (beresp.http.Surrogate-Control ~ "ESI/1.0") {
        unset beresp.http.Surrogate-Control;
        set beresp.do_esi = true;
    }

    // Allow stale content, in case the backend goes down or cache is not fresh any more
    // make Varnish keep all objects for 1 hours beyond their TTL
    set beresp.grace = 1h;

    return (deliver);

}

// Handle purge
// You may add FOSHttpCacheBundle tagging rules
// See http://foshttpcache.readthedocs.org/en/latest/varnish-configuration.html#id4
sub ez_purge {

    if (req.method == "BAN") {
        if (!client.ip ~ invalidators) {
            return (synth(405, "Method not allowed"));
        }

        if (req.http.X-Location-Id) {
            ban("obj.http.X-Location-Id ~ " + req.http.X-Location-Id);
            if (client.ip ~ debuggers) {
                set req.http.X-Debug = "Ban done for content connected to LocationId " + req.http.X-Location-Id;
            }
            return (synth(200, "Banned"));
        }
    }
}

sub vcl_deliver {
    if (obj.hits > 0) {
        set resp.http.X-Cache = "HIT";
        set resp.http.X-Cache-Hits = obj.hits;
    } else {
        set resp.http.X-Cache = "MISS";
    }
}
