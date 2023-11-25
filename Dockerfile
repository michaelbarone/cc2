FROM node:18.17.0-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev
COPY src ./src
COPY http ./http
COPY ./tsconfig.json .
EXPOSE 9876
CMD npm run start:tsc
