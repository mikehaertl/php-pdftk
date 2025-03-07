FROM wodby/php:8.2

USER root

RUN apk add openjdk8 \
    && wget -O /usr/bin/pdftk.jar https://gitlab.com/pdftk-java/pdftk/-/jobs/924565145/artifacts/raw/build/libs/pdftk-all.jar \
    && echo $'#!/usr/bin/env sh \njava -jar "$0.jar" "$@" \n' > /usr/bin/pdftk \
    && chmod +x /usr/bin/pdftk
