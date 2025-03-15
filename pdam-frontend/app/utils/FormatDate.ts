function FormatDate(date: string | Date | null): string {
    if(!date) return "--:--"

    const option: Intl.DateTimeFormatOptions = {
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric"
    }

    return new Date(date).toLocaleDateString("id-ID", option)
}

export { FormatDate }