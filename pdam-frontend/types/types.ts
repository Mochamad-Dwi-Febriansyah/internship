export interface InternshipForm {
    name: string;
    email: string;
    phone: string;
    resume: File | null;
    [key: string]: any; // Opsional, jika ada data lain
  }
export interface LaporanAkhirForm {
    title: string;
    report: string;  
    assessment_report_file: File | null;
    final_report_file: File | null; 
    photo: File | null; 
    video: string;  
    [key: string]: any; // Opsional, jika ada data lain
  }


export interface PaginationLinks {
  url: string | null,
  label: string,
  active: boolean
}

export interface PaginationMeta<T> {
  current_page: number
  data: T[]
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  links: PaginationLinks[]
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}

export interface ApiResponse<T> {
  status: string
  message: string
  data: PaginationMeta<T>
}

export interface ApiResponseSingle<T> {
  status: string
  message: string
  data: T
}

export interface ApiResponseAction {
  status: string
  message: string 
}