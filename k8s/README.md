# Kubernetes local

Corre la app completa en un cluster local de Kubernetes, igual que el ejemplo de la cátedra:

- **API:** `ReplicaSet` de 3 réplicas, `Service` NodePort y `HorizontalPodAutoscaler` (de 3 a 10 pods según el CPU).
- **Frontend:** `ReplicaSet` de 2 réplicas y `Service` NodePort.
- **PostgreSQL:** un `Deployment` con volumen persistente, un `Secret` con la contraseña y un `Service` interno.
- Las imágenes se construyen localmente (`:local`), sin necesidad de Docker Hub.

## Requisitos

- **Docker Desktop** con Kubernetes activado (Settings, Kubernetes, Enable Kubernetes). Con minikube o kind ver la nota del final.
- **kubectl**.

```bash
kubectl get nodes
```

## 1. Construir las imágenes locales

Desde la raíz de cada repo. Como el cluster expone la API en `http://localhost:30081/api`, el frontend se construye con esa URL (Vite la "hornea" al buildear):

```bash
# en el repo del backend
docker build -t cartelera-api:local .

# en el repo del frontend
docker build --build-arg VITE_API_BASE_URL=http://localhost:30081/api -t cartelera-frontend:local .
```

## 2. Levantar todo

Desde la raíz del repo del backend:

```bash
kubectl apply -k k8s/
kubectl get pods -n cartelera --watch
```

Esperar a que `postgres`, los 3 pods `api` y los 2 `frontend` estén en `Running` y `READY 1/1`.

## 3. Abrir la app

- App: `http://localhost:30080`
- API: `http://localhost:30081/api/categoria`
- Health check: `http://localhost:30081/api/healthcheck`

Usuarios: `admin` / `admin123` (puede crear categorías) y `demo` / `demo123` (solo lectura).

## 4. Autoscaling (HPA)

El HPA necesita **metrics-server** para leer el CPU de los pods. Docker Desktop no lo trae:

```bash
kubectl apply -f https://github.com/kubernetes-sigs/metrics-server/releases/latest/download/components.yaml
kubectl patch -n kube-system deployment metrics-server --type=json -p '[{"op":"add","path":"/spec/template/spec/containers/0/args/-","value":"--kubelet-insecure-tls"}]'
```

Después de un minuto, `kubectl get hpa -n cartelera` muestra el uso de CPU en `TARGETS` (si dice `<unknown>`, todavía no hay métricas).

Para ver cómo escala, generar carga y mirar el HPA en otra terminal:

```bash
kubectl run load-generator -n cartelera -it --rm --image=busybox:1.36 --restart=Never -- /bin/sh -c "while sleep 0.01; do wget -q -O- http://api/api/healthcheck; done"
kubectl get hpa -n cartelera --watch
```

Al cortar la carga (Ctrl+C), los pods bajan de a uno después de 5 minutos.

## 5. Comandos útiles

```bash
kubectl get all -n cartelera                      # ver todo lo que hay
kubectl logs -n cartelera -l app=api              # logs de la API
kubectl describe pod -n cartelera <pod>           # detalle de un pod con problemas
kubectl delete pod -n cartelera <pod>             # el ReplicaSet crea otro para mantener las réplicas
```

## 6. Apagar y borrar

```bash
kubectl delete -k k8s/
```

Esto borra también el namespace y el volumen de la base de datos.

## Qué es cada archivo

| Archivo | Qué define |
|---|---|
| `namespace.yaml` | El namespace `cartelera`, que agrupa los recursos de la app. |
| `postgres.yaml` | Secret con la contraseña, volumen persistente, Deployment y Service de PostgreSQL. |
| `api.yaml` | ConfigMap, ReplicaSet de la API, Service NodePort y HPA. |
| `frontend.yaml` | ReplicaSet y Service NodePort del frontend. |
| `kustomization.yaml` | Une todo y genera el ConfigMap con el script SQL. |
| `peliculas_db.sql` | Copia del script que inicializa la base. |

## Nota: minikube y kind

Las imágenes `:local` tienen que estar dentro del cluster: en minikube, `minikube image load cartelera-api:local` (y lo mismo con el frontend); en kind, `kind load docker-image cartelera-api:local`. Además, minikube no publica los NodePort en `localhost`: se abren con `minikube service -n cartelera api --url`, y el frontend habría que construirlo con esa URL.
