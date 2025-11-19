import { userRoutes } from "./users/routes"
import classroomRoutes from "./classrooms/routes"
import studentRoutes from "./students/routes"

const masterDataChildren = [].concat(
    userRoutes,
    classroomRoutes,
    studentRoutes
)
export const MasterDataRoutes = [
    { path: '/master-data', name: 'Master Data', children: masterDataChildren },
]
