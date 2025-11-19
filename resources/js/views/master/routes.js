import { userRoutes } from "./users/routes"

const masterDataChildren = [].concat(
    userRoutes
)
export const MasterDataRoutes = [
    { path: '/master-data', name: 'Master Data', children: masterDataChildren },
]