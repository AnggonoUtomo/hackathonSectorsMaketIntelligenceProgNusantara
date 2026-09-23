import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import type { CompanyIdentity } from '@/types/company-directory';

export default function CompanyLogo({ company }: { company: CompanyIdentity }) {
    return (
        <Avatar className="size-10 rounded-md border bg-white">
            <AvatarImage
                src={company.logoUrl ?? undefined}
                alt={`Logo ${company.name}`}
                className="object-contain p-1"
                referrerPolicy="no-referrer"
            />
            <AvatarFallback className="bg-muted text-muted-foreground rounded-md text-xs">{company.symbol.slice(0, 2)}</AvatarFallback>
        </Avatar>
    );
}
