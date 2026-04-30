import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import DocumentCard from '../components/DocumentCard';
import StatusBadge from '../components/StatusBadge';

const ALL_DOCS = [
  {
    id: '1',
    name: 'Työsopimus_2025.pdf',
    date: '24.4.2025',
    status: 'waiting',
    signers: [
      { name: 'Matti Virtanen', email: 'matti@esimerkki.fi', signed: true },
      { name: 'Anna Korhonen', email: 'anna@esimerkki.fi', signed: false },
      { name: 'Pekka Leinonen', email: 'pekka@esimerkki.fi', signed: false },
    ],
  },
  {
    id: '2',
    name: 'Vuokrasopimus_Helsinki.pdf',
    date: '22.4.2025',
    status: 'signed',
    signers: [
      { name: 'Laura Mäkinen', email: 'laura@esimerkki.fi', signed: true },
      { name: 'Jukka Hämäläinen', email: 'jukka@esimerkki.fi', signed: true },
    ],
  },
  {
    id: '3',
    name: 'NDA_Sopimus_2025.pdf',
    date: '20.4.2025',
    status: 'rejected',
    signers: [
      { name: 'Sari Nieminen', email: 'sari@esimerkki.fi', signed: false },
    ],
  },
  {
    id: '4',
    name: 'Kauppasopimus_Q2.pdf',
    date: '18.4.2025',
    status: 'waiting',
    signers: [
      { name: 'Mikko Ojala', email: 'mikko@esimerkki.fi', signed: true },
      { name: 'Tiina Rantanen', email: 'tiina@esimerkki.fi', signed: true },
      { name: 'Ville Laukkanen', email: 'ville@esimerkki.fi', signed: false },
    ],
  },
  {
    id: '5',
    name: 'Yhteistyösopimus_2025.pdf',
    date: '15.4.2025',
    status: 'signed',
    signers: [
      { name: 'Erika Salminen', email: 'erika@esimerkki.fi', signed: true },
      { name: 'Timo Jokinen', email: 'timo@esimerkki.fi', signed: true },
      { name: 'Pirjo Heikkinen', email: 'pirjo@esimerkki.fi', signed: true },
    ],
  },
  {
    id: '6',
    name: 'Osakkuussopimus_2024.pdf',
    date: '10.4.2025',
    status: 'signed',
    signers: [
      { name: 'Harri Laine', email: 'harri@esimerkki.fi', signed: true },
    ],
  },
  {
    id: '7',
    name: 'Konsulttisopimus_Projekti.pdf',
    date: '5.4.2025',
    status: 'waiting',
    signers: [
      { name: 'Jaana Virtanen', email: 'jaana@esimerkki.fi', signed: false },
      { name: 'Risto Koskinen', email: 'risto@esimerkki.fi', signed: false },
    ],
  },
];

export default function DocumentsPage() {
  const navigate = useNavigate();
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');

  const filtered = ALL_DOCS.filter((doc) => {
    const matchStatus = filter === 'all' || doc.status === filter;
    const matchSearch = doc.name.toLowerCase().includes(search.toLowerCase());
    return matchStatus && matchSearch;
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Dokumentit</h1>
          <p className="text-slate-500 text-sm mt-1">{ALL_DOCS.length} dokumenttia yhteensä</p>
        </div>
        <button
          onClick={() => navigate('/upload')}
          className="inline-flex items-center gap-2 bg-blue-900 text-white px-5 py-2.5 rounded-xl font-medium text-sm hover:bg-blue-800 transition-colors shadow-sm self-start sm:self-auto"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
          Lataa uusi
        </button>
      </div>

      {/* Filters + search */}
      <div className="flex flex-col sm:flex-row gap-3">
        <div className="relative flex-1">
          <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Hae dokumentteja..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-300 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          />
        </div>
        <div className="flex items-center gap-2 flex-wrap">
          {[
            { key: 'all', label: 'Kaikki' },
            { key: 'waiting', label: 'Odottaa' },
            { key: 'signed', label: 'Allekirjoitettu' },
            { key: 'rejected', label: 'Hylätty' },
          ].map(({ key, label }) => (
            <button
              key={key}
              onClick={() => setFilter(key)}
              className={`px-3 py-2 rounded-lg text-xs font-medium transition-colors ${
                filter === key
                  ? 'bg-blue-900 text-white'
                  : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
              }`}
            >
              {key === 'all' ? label : <StatusBadge status={key} />}
            </button>
          ))}
        </div>
      </div>

      {/* Grid */}
      {filtered.length === 0 ? (
        <div className="bg-white rounded-2xl border border-slate-200 p-12 text-center">
          <div className="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5}
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p className="text-slate-500 text-sm">Ei dokumentteja löytynyt haullasi.</p>
          <button
            onClick={() => { setFilter('all'); setSearch(''); }}
            className="mt-3 text-blue-700 text-sm hover:underline"
          >
            Tyhjennä haku
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          {filtered.map((doc) => (
            <DocumentCard key={doc.id} doc={doc} />
          ))}
        </div>
      )}
    </div>
  );
}
