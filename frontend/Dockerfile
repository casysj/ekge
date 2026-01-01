# 프론트엔드 Dockerfile (개발용)
FROM node:22-alpine

WORKDIR /app

# package.json 복사 및 의존성 설치
COPY package*.json ./
RUN npm install

# 소스 코드 복사
COPY . .

# Vite 개발 서버 실행
EXPOSE 5173

CMD ["npm", "run", "dev"]
