FROM node:25.8.1-alpine AS builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build:prod


FROM node:25.8.1-alpine

WORKDIR /app

COPY --from=builder /app/build ./build
COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/package.json .

EXPOSE 80

CMD ["node", "--import", "./otel/console-instrumentation.js", "build/index.js"]
