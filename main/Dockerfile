FROM node:alpine

WORKDIR /app

COPY package.json ./
RUN sh -c 'unset NODE_OPTIONS; npm config set fund false && npm install --include=dev'

COPY . .
RUN npm run build:prod

EXPOSE 80

ENV PORT=80
ENV HOST=0.0.0.0
CMD ["node", "--import", "./otel/console-instrumentation.js", "build/index.js"]
