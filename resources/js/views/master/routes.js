import { userRoutes } from "./users/routes"
import classroomRoutes from "./classrooms/routes"
import studentRoutes from "./students/routes"
import subjectRoutes from "./subjects/routes"
import scheduleRoutes from "./schedules/routes"

const masterDataChildren = [].concat(
    userRoutes,
    classroomRoutes,
    studentRoutes,
    subjectRoutes,
    scheduleRoutes
)
export const MasterDataRoutes = [
    { path: '/master-data', name: 'Master Data', children: masterDataChildren },
]
